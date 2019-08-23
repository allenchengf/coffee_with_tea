<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LocationDnsSettingRequest;
use Hiero7\Services\{LocationDnsSettingService, DomainGroupService};
use Hiero7\Repositories\CdnRepository;
use Hiero7\Models\{Domain, Cdn, LocationDnsSetting, DomainGroup, LocationNetwork, CdnProvider};
use Hiero7\Traits\{OperationLogTrait, PaginationTrait};
use Hiero7\Enums\{InputError, InternalError};

class LocationDnsSettingController extends Controller
{
    use OperationLogTrait;
    use PaginationTrait;
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
     * get Group 的 iRoute 設定列表
     *
     * @param Request $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function indexGroups(Request $request, DomainGroup $domainGroup)
    {
        // 初始換頁資訊
        $last_page = $current_page = $per_page = $total = null;

        // 取得換頁資訊
        list($perPage, $columns, $pageName, $currentPage) = $this->getPaginationInfo($request->get('per_page'), $request->get('current_page'));

        $domainGroupCollection = $domainGroup->where(['user_group_id' => $this->getUgid($request)]);

        if (! is_null($perPage)) { // 換頁
            $domainGroupCollection = $domainGroupCollection->paginate($perPage, $columns, $pageName, $currentPage);

            $last_page = $domainGroupCollection->lastPage();
            $current_page = $domainGroupCollection->currentPage();
            $per_page = $perPage;
            $total = $domainGroupCollection->total();
        } else { // 全部列表
            $domainGroupCollection = $domainGroupCollection->get();
        }

        $domain_groups = [];
        foreach($domainGroupCollection as $domainGroupModel){
            $domain_groups[] = $this->domainGroupService->indexGroupIroute($domainGroupModel);
        }

        return $this->response('', null, compact('current_page', 'last_page', 'per_page', 'total', 'domain_groups'));
    }


    /**
     * get 孤兒 Domain 的 iRoute 設定列表
     *
     * @param Request $request
     * @param Domain $domain
     * @return void
     */
    public function indexDomains(Request $request, Domain $domain)
    {
        // 初始換頁資訊
        $last_page = $current_page = $per_page = $total = null;

        // 取得換頁資訊
        list($perPage, $columns, $pageName, $currentPage) = $this->getPaginationInfo($request->get('per_page'), $request->get('current_page'));

        // 找出孤兒: domain_group_mapping.domain_group_id = null
        $domainsCollection = $domain
                            ->select("domains.*","domain_group_mapping.domain_group_id")
                            ->leftJoin('domain_group_mapping', 'domain_group_mapping.domain_id', '=', 'domains.id')
                            ->where(['user_group_id' => $this->getUgid($request)])
                            ->whereNull('domain_group_mapping.domain_group_id');
                            
        if (! is_null($perPage)) { // 換頁
            $domainsCollection = $domainsCollection->paginate($perPage, $columns, $pageName, $currentPage);

            $last_page = $domainsCollection->lastPage();
            $current_page = $domainsCollection->currentPage();
            $per_page = $perPage;
            $total = $domainsCollection->total();
        } else { // 全部列表
            $domainsCollection = $domainsCollection->get();
        }

        foreach($domainsCollection as $domainModel){
            $domainModel->location_network = $this->locationDnsSettingService->indexByDomain($domainModel);
        }
        
        $domains = $domainsCollection->flatten();

        return $this->response('', null, compact('current_page', 'last_page', 'per_page', 'total', 'domains'));
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
    public function editSetting(LocationDnsSettingRequest $request, Domain $domain, LocationNetwork $locationNetwork)
    {
        $message = '';
        $error = '';

        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $result = $this->locationDnsSettingService->decideAction($request->cdn_provider_id, $domain, $locationNetwork);

        if ($result === 'differentGroup') {
            return $this->setStatusCode(400)->response($message,InputError::WRONG_PARAMETER_ERROR,'');
        }

        if ($result == false) {
            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }

        $data = $this->locationDnsSettingService->indexByDomain($domain);

        $this->createEsLog($this->getJWTPayload()['sub'], "IRoute", "update", "IRouteCDN");

        return $this->response($message,$error,$data);
    }
}
