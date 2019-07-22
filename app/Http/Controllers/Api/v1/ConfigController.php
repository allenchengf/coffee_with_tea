<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};
use Hiero7\Services\{ConfigService,DnsPodRecordSyncService};
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Hiero7\Traits\DomainHelperTrait;
use DB;
use Hiero7\Enums\{InputError,DbError};
class ConfigController extends Controller
{
    use DomainHelperTrait;    
    protected $configService;

    public function __construct(ConfigService $configService,DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->configService = $configService;
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
    }
    
    public function index(Request $request,Domain $domain, CdnProvider $cdnProvider,DomainGroup $domainGroup)
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

    public function import(Request $request,Domain $domain,CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {
        $userGroupId = $this->getUgid($request);
        
        DB::beginTransaction();
        $domain->where('user_group_id',$userGroupId)->delete();
        $cdnProvider->where('user_group_id',$userGroupId)->delete();
        $domainGroup->where('user_group_id',$userGroupId)->delete();

        $importData = $this->formateData($request);
        $checkResult = $this->configService->checkDomainFormate($importData);

        //不符合格式 return false 並 rollback
        if(isset($checkResult['errorData']))
        {
            DB::rollback();
            return $this->setStatusCode(400)->response('',InputError::WRONG_PARAMETER_ERROR,$checkResult);
        }
        //新增 Domain
        $resultDomains = $this->configService->insert($importData['domains'],$domain);
        if(isset($resultDomains['errorData'])){
            DB::rollback();
            return $this->setStatusCode(400)->response('',DbError::INSERT_GOT_SOME_PROBLEM,$resultDomains);
        }
        //新增 cdnProvider
        $resultCdnProviders = $this->configService->insert($importData['cdnProviders'], $cdnProvider);
        if(isset($resultCdnProviders['errorData'])){
            DB::rollback();
            return $this->setStatusCode(400)->response('',DbError::INSERT_GOT_SOME_PROBLEM,$resultCdnProviders);
        }
        //新增 cdns
        $resultCdns = $this->configService->insert($importData['cdns'],new Cdn);
        if(isset($resultCdns['errorData'])){
            DB::rollback();
            return $this->setStatusCode(400)->response('',DbError::INSERT_GOT_SOME_PROBLEM,$resultCdns);
        }
        //新增 LocationDns
        $resultLocationDnsSetting = $this->configService->insert($importData['locationDnsSetting'],new LocationDnsSetting);
        if(isset($resultLocationDnsSetting['errorData'])){
            DB::rollback();
            return $this->setStatusCode(400)->response('',DbError::INSERT_GOT_SOME_PROBLEM,$resultLocationDnsSetting);
        }
        //新增 DomainGroup
        $resultDomainGroups = $this->configService->insert($importData['domainGroups'], $domainGroup);
        if(isset($resultDomainGroups['errorData'])){
            DB::rollback();
            return $this->setStatusCode(400)->response('',DbError::INSERT_GOT_SOME_PROBLEM,$resultDomainGroups);
        }

        DB::commit();
        
        $this->callSync($domain, $userGroupId);

        return $this->response('', null,'');
    }

    private function callSync(Domain $domain,Int $userGroupId)
    {
        $domains = $domain->where('user_group_id',$userGroupId)->get();
        
        $result = [];
        foreach($domains as $domainModel){
            $result[] = $this->dnsPodRecordSyncService->syncAndCheckRecords($domainModel);
        }

        return $result;
    }

    private function formateData(Request $request)
    {
        $importData = $request->all();

        $domain =  $this->formateDomainArray($importData['domains']);
        $cdn =  $this->formateCdnArray($importData['domains']);
        $locationDnsSetting =  $this->formateLocationDnsSettingArray($importData['domains']);

        $result = ['domains' => $domain,
                    'cdns' => $cdn,
                    'locationDnsSetting' => $locationDnsSetting,
                    'cdnProviders' => $importData['cdnProviders'],
                    'domainGroups' => $importData['domainGroups']];

        return $result;
    }

    // private function handleDataToDataBase(Array $data,Domain $domain,CdnProvider $cdnProvider,DomainGroup $domainGroup)
    // {
    //     $result = [];

    //     $result['domains'] = $this->configService->insert($data['domains'],$domain);
    //     $result['cdnProviders'] = $this->configService->insert($data['cdnProviders'], $cdnProvider);
    //     $result['cdns'] = $this->configService->insert($data['cdns'],new Cdn);
    //     $result['locationDnsSetting'] = $this->configService->insert($data['locationDnsSetting'],new LocationDnsSetting);
    //     $result['domainGroups'] = $this->configService->insert($data['domainGroups'], $domainGroup);

    //     return $result;
    // }

    private function formateDomainArray(array $domainWithOther)
    {
        $domains = [];
        foreach($domainWithOther as $domain){
            $domains[]  = Arr::except($domain,['cdns','location_dns_settings']);
        }

        return $domains;
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
        
        return $cdns->all();
    }

    private function formateLocationDnsSettingArray(array $domainWithOther)
    {
        $locationDnsSetting = [];
        foreach($domainWithOther as $domain){
            $locationDnsSettingArray = Arr::only($domain,['location_dns_settings']);  
            if(empty($locationDnsSettingArray)){
                continue;
            }
            foreach($locationDnsSettingArray['location_dns_settings'] as &$array){
                $array = Arr::except($array,['domain_id']);
            }
            $locationDnsSetting [] = $locationDnsSettingArray['location_dns_settings'];
        }
        $locationDnsSettings = collect($locationDnsSetting)->collapse();
        
        return $locationDnsSettings->all();
    }

}
