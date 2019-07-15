<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Http\Controllers\Controller;
use Hiero7\Models\{Domain,Cdn,CdnProvider,LocationDnsSetting,DomainGroup};

class ConfigController extends Controller
{
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

//     public function import(Request $request,Domain $domain, Cdn $cdn ,
//                             CdnProvider $cdnProvider,LocationDnsSetting $locationDnsSetting,DomainGroup $domainGroup)
//     {
//         $importData = $request->all();

//         $dataBase = $this->getDataBaseAllSetting($this->getUgid($request),$domain,$cdnProvider,$domainGroup);

//         foreach($datas as $domainData){
//             $domainInsert = Arr::except($domainData,['cdns','cdn_providers','location_dns_settings','domain_group']);

//             // dd($domainInsert);

//             $domain->create($domainInsert);
//             // $domain->id = $domainData['id'];
//             // $domain->user_group_id = $domainData['user_group_id'];
//             // $domain->name = $domainData['name'];
//             // $domain->cname = $domainData['cname'];
//             // $domain->label = $domainData['label']; 
//             // $domain->edited_by = $domainData['edited_by'];
//             // $domain->save();
// // dd($domainData);
//             $this->insertCdn($domainData['cdns'],$cdn);
//             $this->insertCdnProvider($domainData['cdn_providers'],$cdnProvider);
//             $this->insertLocationDnsSettings($domainData['location_dns_settings'],$locationDnsSetting);
//             $this->insertDomainGroup($domainData['domain_group'],$domainGroup);
//         }
        
//         return $this->response('', null, $datas);
//     }

//     private function insertCdn(array $cdns, Cdn $cdn)
//     {
//         foreach($cdns as $cdnModel){
//             $cdn->id = $cdnModel['id'];
//             $cdn->domain_id = $cdnModel['domain_id'];
//             $cdn->cdn_provider_id = $cdnModel['cdn_provider_id'];
//             $cdn->provider_record_id = $cdnModel['provider_record_id'];
//             $cdn->cname = $cdnModel['cname'];
//             $cdn->edited_by = $cdnModel['edited_by'];
//             $cdn->default = $cdnModel['default'];
//             $cdn->save();
//         }
//     }

//     private function insertCdnProvider(array $cdnProviders, CdnProvider $cdnProvider)
//     {

//     }

//     private function insertLocationDnsSettings(array $locationDnsSettings, LocationDnsSetting $locationDnsSetting)
//     {

//     }

//     private function insertDomainGroup(array $domainGroups, DomainGroup $domainGroup)
//     {

//     }
}
