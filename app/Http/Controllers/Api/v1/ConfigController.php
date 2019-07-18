<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};
use Hiero7\Services\ConfigServices;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class ConfigController extends Controller
{
    protected $configServices;

    public function __construct(ConfigServices $configServices)
    {
        $this->configServices = $configServices;
    }
    
    public function get(Request $request,Domain $domain, CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {
        $userGroupId = $this->getUgid($request);
        $result = $this->getDataBaseAllSetting($userGroupId, $domain, $cdnProvider, $domainGroup );
        return $this->response('', null, $result);

    }

    private function getDataBaseAllSetting(int $userGroupId,Domain $domain, CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {
        $domains = $domain->with('cdns','locationDnsSettings')->where('user_group_id',$userGroupId)->get();
        $cdnProviders = $cdnProvider->where('user_group_id',$userGroupId)->get();
        $domainGroups = $domainGroup->where('user_group_id',$userGroupId)->get();

        return compact('domains','cdnProviders','domainGroups');
    }

    public function import(Request $request,Domain $domain, Cdn $cdn ,
                            CdnProvider $cdnProvider,LocationDnsSetting $locationDnsSetting,DomainGroup $domainGroup)
    {
        $result = [];
        $importData = $request->all();

        $dataBase = $this->getDataBaseAllSetting($this->getUgid($request),$domain,$cdnProvider,$domainGroup);
        $dataBase = $this->changeDbFormate($dataBase);

        $domainDbData =  $this->formateDomainArray($dataBase['domains']->toArray());
        $domainImportData =  $this->formateDomainArray($importData['domains']);

        $cdnDbData =  $this->formateCdnArray($dataBase['domains']->toArray());
        $cdnImportData =  $this->formateCdnArray($importData['domains']);

        $locationDnsSettingDbData =  $this->formateLocationDnsSettingArray($dataBase['domains']->toArray());        
        $locationDnsSettingImportData =  $this->formateLocationDnsSettingArray($importData['domains']);

        list($updateData ,$InsertData, $deleteData) = $this->compare($domainImportData, $domainDbData, 'domains');
        $result['domains'] = $this->operationDb($updateData ,$InsertData, $deleteData, new Domain);

        list($updateData ,$InsertData, $deleteData) = $this->compare($importData, $dataBase, 'cdnProviders');
        $result['cdnProvider'] = $this->operationDb($updateData ,$InsertData, $deleteData, new CdnProvider);

        list($updateData ,$InsertData, $deleteData) = $this->compare($cdnImportData, $cdnDbData, 'cdns');
        $result['cdns'] = $this->operationDb($updateData ,$InsertData, $deleteData, new Cdn);

        list($updateData ,$InsertData, $deleteData) = $this->compare($locationDnsSettingImportData, $locationDnsSettingDbData, 'LocationDnsSetting');
        $result['LocationDnsSetting'] = $this->operationDb($updateData ,$InsertData, $deleteData, new LocationDnsSetting);

        list($updateData ,$InsertData, $deleteData) = $this->compare($importData, $dataBase, 'domainGroups');
        $result['domainGroup'] = $this->operationDb($updateData ,$InsertData, $deleteData, new DomainGroup);

        return $this->response('', null, $result);
    }

    //如果三個參數都沒有資料，回傳也會是空[]
    public function operationDb(Collection $updateData ,Collection $InsertData, Collection $deleteData, Model $targetTable)
    {
        $result = [];
        
        if(!$updateData->isEmpty()){
            $result['updateData'] = $this->configServices->update($updateData ,$targetTable);
        }

        if(!$InsertData->isEmpty()){
            $result['InsertData'] = $this->configServices->insert($InsertData ,$targetTable);
        }

        if(!$deleteData->isEmpty()){
            $result['deleteData'] = $this->configServices->delete($deleteData ,$targetTable);
        }

        return $result;
    }

    public function compare(array $importData,array $dataBase,String $index)
    {
        $dataBaseCdnProviderWithHash = $this->formateDataWithHash($dataBase["$index"]);
        $importCdnProviderWithHash = $this->formateDataWithHash($importData["$index"]);
        
        list($updateData ,$InsertData, $deleteData) = $this->configServices->getDifferent(collect($importCdnProviderWithHash)->keyBy('hash'),
                                                                                        collect($dataBaseCdnProviderWithHash)->keyBy('hash'));

        return [$updateData ,$InsertData, $deleteData];
    }


    private function formateDomainArray(array $domainWithOther)
    {
        $domains = [];
        foreach($domainWithOther as $domain){
            $domains[]  = Arr::except($domain,['cdns','location_dns_settings']);
        }
        $result = ['domains' => $domains];

        return $result;
    }

    private function formateCdnArray(array $domainWithOther)
    {
        $cdns = [];
        foreach($domainWithOther as $domain){
            $cdnsArray = Arr::only($domain,['cdns']); 
            if(empty($cdnsArray)){
                continue;
            }        
            $cdns [] = $cdnsArray['cdns'];
        }
        $cdns = collect($cdns)->collapse();
        
        return ['cdns' => $cdns->all()];
    }

    private function formateLocationDnsSettingArray(array $domainWithOther)
    {
        $locationDnsSetting = [];
        foreach($domainWithOther as $domain){
            $locationDnsSettingArray = Arr::only($domain,['location_dns_settings']);  
            if(empty($locationDnsSettingArray)){
                continue;
            } 
            $locationDnsSetting [] = $locationDnsSettingArray['location_dns_settings'];
        }
        $locationDnsSettings = collect($locationDnsSetting)->collapse();
        
        return ['LocationDnsSetting' => $locationDnsSettings->all()];
    }

    private function changeDbFormate(array $dataBase)
    {
        
        foreach($dataBase['cdnProviders'] as $key => $value){
            $dataBase['cdnProviders'][$key] = $value->toArray();
        }
        $dataBase['cdnProviders'] = $dataBase['cdnProviders']->toArray();

        foreach($dataBase['domainGroups'] as $key => $value){
            $dataBase['domainGroups'][$key] = $value->toArray();
        }
        $dataBase['domainGroups'] = $dataBase['domainGroups']->toArray();

        return $dataBase;
    }

    private function formateDataWithHash(array $dataArray)
    {
        foreach ($dataArray as &$data){
            $dataWithoutId = Arr::except($data, ['id']);
            $data['hash'] = $this->hashData($dataWithoutId);
        }
        return collect($dataArray);
    }

    private function hashData(array $data)
    {
        unset($data['id']);
        return sha1(json_encode($data));
    }
}
