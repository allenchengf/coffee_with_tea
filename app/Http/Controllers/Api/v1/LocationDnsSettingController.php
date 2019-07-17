<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LocationDnsSettingRequest;
use Hiero7\Services\{LocationDnsSettingService, DomainGroupService};
use Hiero7\Repositories\CdnRepository;
use Hiero7\Models\{Domain, Cdn, LocationDnsSetting, DomainGroup, LocationNetwork};
use Hiero7\Enums\{InputError, InternalError};

class LocationDnsSettingController extends Controller
{
    protected $locationDnsSettingService;

    public function __construct(
        LocationDnsSettingService $locationDnsSettingService,
        DomainGroupService $domainGroupService,
        CdnRepository $cdnRepository
    )
    {
        $this->locationDnsSettingService = $locationDnsSettingService;
        $this->domainGroupService = $domainGroupService;
        $this->cdnRepository = $cdnRepository;
    }

    public function indexByDomain(Domain $domain)
    {
        $result = $this->locationDnsSettingService->indexByDomain($domain->id);
        return $this->response('',null,$result);

    }

    public function indexByGroup(Request $request,Domain $domain)
    {
        $user_group_id = $this->getUgid($request);

        $domainGroup = DomainGroup::where(compact('user_group_id'))->get();
        
        $domains = $domain->with('domainGroup')->where(compact('user_group_id'))->get();

        $domains = $domains->filter(function ($item) {
            return $item->domainGroup->isEmpty();
        });

        $domains = $domains->flatten();

        return $this->response('',null,compact('domainGroup','domains'));

    }

    public function indexAll(Request $request,Domain $domain)
    {
        $user_group_id = $this->getUgid($request);

        $domainGroupCollection = DomainGroup::where(compact('user_group_id'))->get();


        foreach($domainGroupCollection as $domainGroupModel){
            $domainGroup[] = $this->domainGroupService->indexGroupIroute($domainGroupModel);
        }

        $domainsCollection = $domain->with('domainGroup')->where(compact('user_group_id'))->get();

        $domainsCollection = $domainsCollection->filter(function ($item) {
            return $item->domainGroup->isEmpty();
        });

        foreach($domainsCollection as $domainModel){
            $domainModel->location_network = $this->locationDnsSettingService->indexByDomain($domainModel->id);
        }
        
        $domains = $domainsCollection->flatten();


        return $this->response('',null,compact('domainGroup','domains'));
    }

    public function editSetting(LocationDnsSettingRequest $request, Domain $domain, LocationNetwork $locationNetworkId)
    {
        $message = '';
        $error = '';

        $cdnModel = $this->cdnRepository->indexByWhere(['cdn_provider_id' => $request->get('cdn_provider_id'), 'domain_id' => $domain->id])->first();
        if (is_null($cdnModel)) {
            return $this->setStatusCode(400)->response($message,InputError::WRONG_PARAMETER_ERROR,'');
        }

        $data = [
            'cdn_id' => $cdnModel->id,
            'edited_by' => $this->getJWTPayload()['uuid']
        ];

        $existLocationDnsSetting = $this->checkExist($domain, $locationNetworkId);

        if (!collect($existLocationDnsSetting)->isEmpty()) {
            $result = $this->locationDnsSettingService->updateSetting($data, $domain, $cdnModel, $existLocationDnsSetting);
        } else {
            $result = $this->locationDnsSettingService->createSetting($data, $domain, $cdnModel, $locationNetworkId);
        }

        if ($result == false) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }
        
        $data = $this->locationDnsSettingService->indexByDomain($domain->id);

        return $this->response($message,$error,$data);
    }

    private function checkCdnIfExist(int $cdnId, Domain $domain)
    {
        return $domain->cdns()->where('id', $cdnId)->first();
    }

    private function checkExist(Domain $domain,LocationNetwork $locationNetwork)
    {
        $cdnId = Cdn::where('domain_id',$domain->id)->pluck('id');
        return LocationDnsSetting::where('location_networks_id',$locationNetwork->id)->whereIn('cdn_id',$cdnId)->first();
        
    }
}
