<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LocationDnsSettingRequest;
use Hiero7\Services\{LocationDnsSettingService, DomainGroupService};
use Hiero7\Repositories\CdnRepository;
use Hiero7\Models\{Domain, Cdn, LocationDnsSetting, DomainGroup, LocationNetwork, CdnProvider};
use Hiero7\Traits\OperationLogTrait;
use Hiero7\Enums\{InputError, InternalError};

class LocationDnsSettingController extends Controller
{
    use OperationLogTrait;
    protected $locationDnsSettingService;
    protected $status;

    public function __construct(
        LocationDnsSettingService $locationDnsSettingService,
        DomainGroupService $domainGroupService,
        CdnRepository $cdnRepository
    )
    {
        $this->locationDnsSettingService = $locationDnsSettingService;
        $this->domainGroupService = $domainGroupService;
        $this->cdnRepository = $cdnRepository;
        $this->status = (env('APP_ENV') !== 'testing') ?? false;
    }

    public function indexByDomain(Domain $domain)
    {
        $result = $this->locationDnsSettingService->indexByDomain($domain);
        return $this->response('',null,$result);

    }

/**
 * get Group/孤兒 Domain 名單列表 的 function
 * 
 * 有回傳 cdn provider 是為了前端多給的。
 * 
 * @param Request $request
 * @param Domain $domain
 * @return void
 */
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

/**
 * get Group/孤兒 Domain 的 iRoute 設定列表 function
 *
 * @param Request $request
 * @param Domain $domain
 * @return void
 */
    public function indexAll(Request $request,Domain $domain)
    {
        $user_group_id = $this->getUgid($request);

        $domainGroupCollection = DomainGroup::where(compact('user_group_id'))->get();

        $domainGroup = [];
        foreach($domainGroupCollection as $domainGroupModel){
            $domainGroup[] = $this->domainGroupService->indexGroupIroute($domainGroupModel);
        }
        $domainsCollection = $domain->with('domainGroup')->where(compact('user_group_id'))->get();

        //找出孤兒
        $domainsCollection = $domainsCollection->filter(function ($item) {
            return $item->domainGroup->isEmpty();
        });

        foreach($domainsCollection as $domainModel){
            $domainModel->location_network = $this->locationDnsSettingService->indexByDomain($domainModel);
        }

        $domains = $domainsCollection->flatten();


        return $this->response('',null,compact('domainGroup','domains'));
    }
/**
 * 新增/修改 iRoute 設定的 function
 * 
 * 拿 cdn_provider_id 換到該 domain 下的 cdn ，再判斷要走 update 還是 create 。
 * 
 * @param LocationDnsSettingRequest $request
 * @param Domain $domain
 * @param LocationNetwork $locationNetworkId
 * @return void
 */
    public function editSetting(LocationDnsSettingRequest $request, Domain $domain, LocationNetwork $locationNetworkId)
    {
        $message = '';
        $error = '';

        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $cdnModel = $this->cdnRepository->indexByWhere(['cdn_provider_id' => $request->get('cdn_provider_id'), 'domain_id' => $domain->id])->first();

        if (is_null($cdnModel)) {
            return $this->setStatusCode(400)->response($message,InputError::WRONG_PARAMETER_ERROR,'');
        }

        $data = [
            'cdn_id' => $cdnModel->id,
            'edited_by' => $this->getJWTPayload()['uuid']
        ];

        $existLocationDnsSetting = $this->checkSettingExist($domain, $locationNetworkId);

        if (!collect($existLocationDnsSetting)->isEmpty()) {
            $result = $this->locationDnsSettingService->updateSetting($data, $domain, $cdnModel, $existLocationDnsSetting);
        } else {
            $result = $this->locationDnsSettingService->createSetting($data, $domain, $cdnModel, $locationNetworkId);
        }

        if ($result == false) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }

        $data = $this->locationDnsSettingService->indexByDomain($domain);

        $this->createEsLog($this->getJWTPayload()['sub'], "IRoute", "update", "IRouteCDN");

        return $this->response($message,$error,$data);
    }

    private function checkCdnIfExist(int $cdnId, Domain $domain)
    {
        return $domain->cdns()->where('id', $cdnId)->first();
    }

    private function checkSettingExist(Domain $domain,LocationNetwork $locationNetwork)
    {
        $cdnId = Cdn::where('domain_id',$domain->id)->pluck('id');
        return LocationDnsSetting::where('location_networks_id',$locationNetwork->id)->whereIn('cdn_id',$cdnId)->first();

    }
}
