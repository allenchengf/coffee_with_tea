<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Services\{LocationDnsSettingService,DomainGroupService};
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Http\Request;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\{Domain,Cdn,LocationDnsSetting,DomainGroup, CdnProvider};
use App\Http\Requests\LocationDnsSettingRequest;

class LocationDnsSettingController extends Controller
{
    use OperationLogTrait;
    protected $locationDnsSettingService;
    protected $status;

    public function __construct(LocationDnsSettingService $locationDnsSettingService,DomainGroupService $domainGroupService)
    {
        $this->locationDnsSettingService = $locationDnsSettingService;
        $this->domainGroupService = $domainGroupService;
        $this->status = (env('APP_ENV') !== 'testing') ?? false;
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
        //取每個 Group 所有的 cdn list 
        foreach($domainGroup as $domainGroupModel ){
            $cdnProvider = $domainGroupModel->domains()->first()->cdnProvider()->get();
            $domainGroupModel->cdn_provider = $cdnProvider;
        }

        $domains = $domain->with('cdnProvider','domainGroup')->where(compact('user_group_id'))->get();
        //取沒有 Group 的 domain
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

        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $cdnModel = $this->checkCdnIfExist($request->get('cdn_id'), $domain);

        if (!$cdnModel) {
            return $this->setStatusCode(400)->response($message,InputError::WRONG_PARAMETER_ERROR,'');
        }

        $existLocationDnsSetting = $this->checkExist($domain, $locationNetworkId);

        if (!collect($existLocationDnsSetting)->isEmpty()) {
            $result = $this->locationDnsSettingService->updateSetting($request->all(),$domain,$cdnModel, $existLocationDnsSetting);
        } else {
            $result = $this->locationDnsSettingService->createSetting($request->all(), $domain, $cdnModel ,$locationNetworkId);
        }

        if ($result == false) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }

        $data = $this->locationDnsSettingService->indexByDomain($domain->id);

        $this->createEsLog($this->getJWTPayload()['sub'], "IRoute", "update", "IRouteCDN");

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
