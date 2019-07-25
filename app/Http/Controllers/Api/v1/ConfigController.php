<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup,DomainGroupMapping};
use Hiero7\Services\{ConfigService,DnsPodRecordSyncService};
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Hiero7\Traits\DomainHelperTrait;
use DB;
use Cache;
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
        $domainGroups = $domainGroup->where('user_group_id',$userGroupId)->with('mapping')->get();

        return compact('domains','cdnProviders','domainGroups');
    }

    public function import(Request $request,Domain $domain,CdnProvider $cdnProvider,DomainGroup $domainGroup)
    {     
        $userGroupId = $this->getUgid($request);

        Cache::put("Config_userGroupId$userGroupId" , true , env('CONFIG_WAIT_TIME'));        
        DB::beginTransaction();

        $this->deleteLocationDnsSetting($domain,$cdnProvider,$userGroupId);
        $domain->where('user_group_id',$userGroupId)->delete();
        $cdnProvider->where('user_group_id',$userGroupId)->delete();
        $domainGroup->where('user_group_id',$userGroupId)->delete();

        $importData = $this->formateDataAndCheckCdn($request);

        if(isset($importData['errorData'])){
            DB::rollback();
            Cache::forget("Config_userGroupId$userGroupId");
            return $this->setStatusCode(400)->response('',InputError::WRONG_PARAMETER_ERROR,$importData);
        }

        $checkDomainResult = $this->configService->checkDomainFormate($importData);

        //不符合格式 return false 並 rollback
        if(isset($checkDomainResult['errorData'])){
            DB::rollback();
            Cache::forget("Config_userGroupId$userGroupId");
            return $this->setStatusCode(400)->response('',InputError::WRONG_PARAMETER_ERROR,$checkDomainResult);
        }

        //新增 Domain
        $this->configService->insert($importData['domains'],$domain, $userGroupId);
        //新增 cdnProvider
        $this->configService->insert($importData['cdnProviders'], $cdnProvider, $userGroupId);
        //新增 cdns
        $this->configService->insert($importData['cdns'],new Cdn, $userGroupId);
        //新增 LocationDns 
        $this->configService->insert($importData['locationDnsSetting'],new LocationDnsSetting, $userGroupId);
        //新增 DomainGroup
        $this->configService->insert($importData['domainGroups'], $domainGroup, $userGroupId);
        //新增 DomainGroupMapping
        $this->configService->insert($importData['domainGroupsMapping'], new DomainGroupMapping, $userGroupId);

        DB::commit();
        
        $this->callSync($domain, $userGroupId);

        Cache::forget("Config_userGroupId$userGroupId");
        return $this->response("Success", null,'');
    }

    private function deleteLocationDnsSetting(Domain $domain, CdnProvider $cdnProvider, int $userGroupId)
    {
        $domainId = $domain->where('user_group_id',$userGroupId)->pluck('id');
        $cdnProviderId = $cdnProvider->where('user_group_id',$userGroupId)->pluck('id');
        $cdnId = Cdn::whereIn('domain_id',$domainId)->whereIn('cdn_provider_id',$cdnProviderId)->pluck('id');
        
        return LocationDnsSetting::whereIn('cdn_id',$cdnId)->delete();
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

    private function formateDataAndCheckCdn(Request $request)
    {
        $importData = $request->all();

        $domain =  $this->formateDomainArray($importData['domains']);
        $checkCdnResult = $this->configService->checkCdnHaveDefault($importData['domains']);

        if(isset($checkCdnResult['errorData'])){
            return $checkCdnResult;
        }

        $cdn =  $this->formateCdnArray($importData['domains']);
        $locationDnsSetting =  $this->formateLocationDnsSettingArray($importData['domains']);
        list($domainGroup,$domainGroupMapping) = $this->formateDomainGroupMapping($importData['domainGroups']);

        $cdnProvider= $this->formateCdnProvider($importData['cdnProviders']);
        
        $result = ['domains' => $domain,
                    'cdns' => $cdn,
                    'locationDnsSetting' => $locationDnsSetting,
                    'cdnProviders' => $cdnProvider,
                    'domainGroups' => $domainGroup,
                    'domainGroupsMapping' => $domainGroupMapping];

        return $result;
    }

    private function formateCdnProvider(Array $cdnProviders)
    {
        foreach($cdnProviders as &$cdnProvidersModel){
            if($cdnProvidersModel['status'] == 'true'){
                $cdnProvidersModel['status'] = "active";
            }else{
                $cdnProvidersModel['status'] = "stop";
            }
        }

        return $cdnProviders;
    }
    private function formateDomainGroupMapping(array $domainGroupWithMapping)
    {
        $domainGroupArray = [];
        $domainGroupMapping = [];
        foreach($domainGroupWithMapping as $domainGroup){
            $mappingArray = array_only($domainGroup,['mapping']);
            $domainGroupMapping[] = $mappingArray['mapping'];
            $domainGroupArray[] =array_except($domainGroup,['mapping']);
        }

        return [$domainGroupArray,array_collapse($domainGroupMapping)];
    }

    private function formateDomainArray(array $domainWithOther)
    {
        $domains = [];
        foreach($domainWithOther as $domain){
            $domains[]  = array_except($domain,['cdns','location_dns_settings']);
        }

        return $domains;
    }

    private function formateCdnArray(array $domainWithOther)
    {
        $cdns = [];
        foreach($domainWithOther as $domain){
            $cdnsArray = array_only($domain,['cdns']); 

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
            $locationDnsSettingArray = array_only($domain,['location_dns_settings']);  
            if(empty($locationDnsSettingArray)){
                continue;
            }
            foreach($locationDnsSettingArray['location_dns_settings'] as &$array){
                $array = array_except($array,['domain_id']);
            }
            $locationDnsSetting [] = $locationDnsSettingArray['location_dns_settings'];
        }
        $locationDnsSettings = collect($locationDnsSetting)->collapse();
        
        return $locationDnsSettings->all();
    }

}
