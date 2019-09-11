<?php

namespace Hiero7\Services;

use App\Http\Requests\DomainGroupRequest;
use Hiero7\Models\{Domain, DomainGroup, LocationDnsSetting, LocationNetwork};
use Hiero7\Repositories\{DomainGroupRepository, CdnRepository};
use Hiero7\Services\{CdnService, LocationDnsSettingService};
use Illuminate\Database\Eloquent\Collection;
use Hiero7\Enums\InputError;

class DomainGroupService
{
    protected $domainGroupRepository;
    protected $cdnRepository;

    public function __construct(
        DomainGroupRepository $domainGroupRepository,
        CdnRepository $cdnRepository,
        CdnService $cdnService,
        LocationDnsSettingService $locationDnsSettingService
    )
    {
        $this->domainGroupRepository = $domainGroupRepository;
        $this->cdnRepository = $cdnRepository;
        $this->cdnService = $cdnService;
        $this->locationDnsSettingService = $locationDnsSettingService;
    }

    public function index(int $userGroupId)
    {
        $groupLists = $this->domainGroupRepository->indexByUserGroup($userGroupId);

        $groupLists = $groupLists->each(function ($item, $key) {
            $domainModel = $item->domains()->first();

            if ($domainModel == null) {
                return false;
            }

            $item->setAttribute('default_cdn_name', $domainModel->getDefaultCdnProvider()->name);
        });

        return $groupLists;
    }

    /**
     * 拿 DomainGroup 去換 iRoute 設定
     *
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function indexGroupIroute(DomainGroup $domainGroup)
    {
        $domainGroup->location_network = $this->locationDnsSettingService->indexByDomain($domainGroup->domains()->first());
        return $domainGroup;
    }

    /**
     * 取得 DomainGroup 和 Domain 的關聯。在各自從 Domain 找 Cdn，再找 Cdn Provider 的名字。
     *
     * @param integer $domainGroup
     * @return void
     */
    public function indexByDomainGroupId(DomainGroup $domainGroup)
    {
        foreach ($domainGroup->domains as $key => $domain) {
            $domain->cdnProvider;
        }
        $domainGroup->setAttribute('default_cdn_name', $domain->getDefaultCdnProvider()->name);
        return $domainGroup;
    }

    /**
     * Create Group function
     *
     * 先檢查 Domain 和 操作人的 user_group_id 是否相同，若 user_group_id ＝ 1 例外。
     * 若 user_group_id = 1 會依照欲加入的 Domain 的 user_group_id 給與新建立的 Group 相同 user_group_id。
     * @param array $request
     */
    public function create(DomainGroupRequest $request)
    {
        $domainModle = Domain::where('id', $request->domain_id)->first();

        if ($domainModle->cdns->isEmpty()) {
            return 'NoneCdn';
        }

        $request->user_group_id == 1 ? $request->user_group_id = $domainModle->user_group_id : $request->user_group_id;

        if ($request->user_group_id != $domainModle->user_group_id) {
            return 'differentGroup';
        }

        $result = $this->domainGroupRepository->create($request);

        if (!$result) {
            return 'exist';
        }

        return $result->domains;
    }

    /**
     * 新增 Domain 進 Group，會先檢查 Domain 本身的 Cdn Provider 是否相同，再去修改 Domain 的 Default CDN 和 iRoute 設定。
     *
     * @param array $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function createDomainToGroup(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $checkDomainCdnSetting = $this->compareDomainCdnSetting($domainGroup, $request->domain_id);

        if (!$checkDomainCdnSetting) {
            return false;
        }

        if (!$this->changeCdnDefault($domainGroup, $request->domain_id, $request->edited_by)) {
            return 'cdnError';
        }

        if (!empty($this->changeIrouteSetting($domainGroup, $request->domain_id))) {
            return 'iRouteError';
        }

        return $this->domainGroupRepository->createDomainToGroup($request, $domainGroup->id);
    }

    /**
     * 純 update Group function
     *  
     *  判斷使用者和要修改的 Group 是否為相同 user_group_id。
     *  
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function edit(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $result = $this->checkUserGroupId($request, $domainGroup);

        return $result ? $this->domainGroupRepository->update($request, $domainGroup->id) : $result;
    }

    /**
     * 純 刪除 Group function
     * 
     * 判斷使用者和要修改的 Group 是否為相同 user_group_id。
     *
     * @param integer $domainGroupId
     * @return void
     */
    public function destroy(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $result = $this->checkUserGroupId($request, $domainGroup);
        
        return $result ? $this->domainGroupRepository->destroy($domainGroup->id) : $result;
    }

    /**
     * 從 Group 移除某個 Domain
     *
     * 先判斷是否為該 Group 內最後一個 Domain，
     * 
     * @param DomainGroup $domainGroup
     * @param Domain $domain
     * @return void
     */
    public function destroyByDomainId(DomainGroupRequest $request, DomainGroup $domainGroup, Domain $domain)
    {
        $result = $this->checkUserGroupId($request, $domainGroup);
        
        $domainCollection = $domainGroup->domains;

        if ($domainCollection->count() == 1) {
            return false;
        }

        return $result ? $this->domainGroupRepository->destroyByDomainId($domainGroup->id, $domain->id) : $result;
    }

    /**
     * 比較 domainGroup 的 cdn 是否和要被加進去的 domain 的 cdn 設定相同
     *
     * @param DomainGroup $domainGroup
     * @param [type] $targetDomainId
     * @return void
     */
    public function compareDomainCdnSetting(DomainGroup $domainGroup, $targetDomainId)
    {
        $controlDomain = $domainGroup->domains;
        $controlCdnProvider = $controlDomain[0]->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');

        try {
            $targetCdnProvider = Domain::find($targetDomainId)->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $targetCdnProvider = [];
        }
        $different = $controlCdnProvider->diff($targetCdnProvider);
        return !$different->isEmpty() ? false : true;
    }

    /**
     * 修改 要被加入 domainGroup 的 domain 的 defaultCdn
     *
     * @param DomainGroup $domainGroup
     * @param integer $domainId
     * @param string $editedBy
     * @return void
     */
    public function changeCdnDefault(DomainGroup $domainGroup, int $domainId, string $editedBy)
    {
        $domain = Domain::find($domainId);
        $getDomainCdnProviderId = $domainGroup->domains()->first()->cdns()->where('default', 1)->first()->cdn_provider_id;
        $targetCdn = $domain->cdns->where('cdn_provider_id', $getDomainCdnProviderId)->first();

        return $this->cdnService->changeDefaultToTrue($domain, $targetCdn, $editedBy);
    }

    /**
     * 修改 要被加入 domainGroup 的 domain 的 iRoute 設定。
     * 
     *
     * @param DomainGroup $domainGroup
     * @param integer $domainId
     * @param string $editedBy
     * @return void
     */
    public function changeIrouteSetting(DomainGroup $domainGroup, int $domainId)
    {
        //取 Group 內的第一個 domain 下的 cdn
        $originCdnSetting = $domainGroup->domains->get(0)->cdns;
        // 取該 cdn 下的所有 iroute
        list($originIrouteSetting,$nonSettingCdn) = $this->getLocationSetting($originCdnSetting);

        if (empty($originIrouteSetting)) {
            return true; //如果 Group 內 cdn 沒有 iroute 設定就不做更改。
        }

        $targetDomain = Domain::find($domainId);
        $result = [];

        // 拿 Group 內的 domain 設定
        foreach ($originIrouteSetting as $iRouteSetting) {
            $response = $this->locationDnsSettingService->decideAction($iRouteSetting->cdn_provider_id, $targetDomain, $iRouteSetting->location);
            
            // 處理回傳的結果，如果是 'differentGroup' 和 false 就會 再傳出去。 
            if (is_string($response)){
                $result[] = $response;
            }else{
                $response ? true : $result[] = $response;
            }
        }

        foreach($nonSettingCdn as $cdnProviderId ){
            $targetCdn = $targetDomain->cdns()->where('cdn_provider_id', $cdnProviderId)->first();
            $targetLocationSetting = $targetCdn->locationDnsSetting;

            if($targetLocationSetting->isEmpty()){
                continue;
            }

            foreach($targetLocationSetting as $locationDnsSetting){
                $this->locationDnsSettingService->destroy($locationDnsSetting);
            }
        }

        return $result;
    }

    public function updateRouteCdn(DomainGroup $domainGroup, LocationNetwork $locationNetwork, int $cdnProviderId, string $editedBy)
    {
        try {
            $returns = [];
            // (int) domain_groups.id 多對多換得 (array) domains.id
            $domainsCollection = collect($this->domainGroupRepository->showByDomainGroupId($domainGroup->id)->domains->toArray());

            // 依 domains 個數迴圈修改 location_dns_settings & DNSPOD
            $domainsCollection->each(function ($v, $k) use (&$cdnProviderId, &$editedBy, &$locationNetwork, &$returns) {
                $domainId = $v['domain_group_mapping']['domain_id'];

                // 查詢全部相同 cdns.domain_id，取其 id 為陣列
                $scopeCdnIds = $this->cdnRepository->indexByWhere(['domain_id' => $domainId])->pluck('id');
                if (is_null($scopeCdnIds)) {
                    $returns[$k] = [
                        'domain' => $v,
                        'cdns' => $scopeCdnIds,
                        'success' => false,
                        'message' => "error: no cdns where ['domain_id' =>  $domainId]"
                    ];
                    return true;
                }

                // 增/改 location_dns_settings & DNSPOD
                // 增/改 > 資料準備
                $targetDomain = Domain::find($domainId);
                $targetCdn = $this->cdnRepository->indexByWhere(['cdn_provider_id' => $cdnProviderId, 'domain_id' => $domainId])->first();
                if (is_null($targetCdn)) {
                    $returns[$k] = [
                        'domain' => $v,
                        'cdns' => $scopeCdnIds,
                        'success' => false,
                        'message' => "error: no cdn where ['cdn_provider_id' => $cdnProviderId, 'domain_id' => $domainId]"
                    ];
                    return true;
                }
                $data = [
                    'cdn_id' => $targetCdn->id,
                    'edited_by' => $editedBy
                ];

                // 查
                $locationDnsSetting = LocationDnsSetting::where('location_networks_id', $locationNetwork->id)->whereIn('cdn_id', $scopeCdnIds)->first();
                if (is_null($locationDnsSetting)) {
                    // 查不到 > 增
                    $result = $this->locationDnsSettingService->createSetting($data, $targetDomain, $targetCdn, $locationNetwork);
                    $returns[$k] = [
                        'domain' => $v,
                        'cdns' => $scopeCdnIds,
                        'success' => true,
                        'message' => "create datum in location_dns_settings, result: " . json_encode($result)
                    ];
                    return true;
                }

                // 查到 > 改
                $result = $this->locationDnsSettingService->updateSetting($data, $targetDomain, $targetCdn, $locationDnsSetting);
                $returns[$k] = [
                    'domain' => $v,
                    'cdns' => $scopeCdnIds,
                    'success' => true,
                    'message' => 'update dnspod & location_dns_settings result: ' . json_encode($result)
                ];
            });
            return $returns;
        } catch(Exception $e) {
            return $e;
        }
    }

    /**
     * 依照 cdn 的設定分辨，哪些 cdn 是有存在  locationDnsSetting table 內，有哪些 cdn 是沒有設定的。
     * 
     * 會回傳 targetIrouteSetting 和 nonSettingCdn 兩個參數。
     * 
     *  targetIrouteSetting 是有設定的 locationDnsSetting 物件
     *  nonSettingCdn 是 cdn 沒有被設定的 cdn_provider_id 
     * 
     * @param Collection $cdnSetting
     * @return void
     */
    private function getLocationSetting(Collection $cdnSetting)
    {
        $nonSettingCdn = [];

        foreach ($cdnSetting as $cdns) {
            // $originLocationDnsSetting = $cdns->locationDnsSetting;
            $originLocationDnsSetting = collect($cdns->locationDnsSetting);
            //檢查該 cdn 是否有存在 locationDnsSetting table
            if ($originLocationDnsSetting->isEmpty()) {
                $nonSettingCdnProviderId = $cdns->cdn_provider_id;
                array_push($nonSettingCdn, $nonSettingCdnProviderId);
                continue;
            }

            //如果有存在 locationDnsSetting table，就要一個一個看。
            $targetIrouteSetting = $originLocationDnsSetting->each(function ($item, $key) use ($cdns){
                $item->cdn_provider_id = $cdns->cdn_provider_id;
            });
        }

        return [$targetIrouteSetting, $nonSettingCdn];
    }

    /**
     * 檢查此 domain 下有沒有 locationDnsSetting
     *
     * @param Domain $domain
     * @param integer $locationNetworkId
     * @return void
     */
    private function checkSettingExist(Domain $domain, int $locationNetworkId)
    {
        $cdnId = $domain->cdns->pluck('id');
        return LocationDnsSetting::where('location_networks_id', $locationNetworkId)->whereIn('cdn_id', $cdnId)->first();
    }

    /**
     * 檢查 使用者 是否操作 相同 userGroup 的 DomainGroup
     *
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    private function checkUserGroupId(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        if ($request->user_group_id != 1 && $request->user_group_id != $domainGroup->user_group_id) {
            return false;
        }

        return true;
    }
}
