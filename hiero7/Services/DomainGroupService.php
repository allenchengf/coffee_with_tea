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

    public function indexGroupIroute(DomainGroup $domainGroup)
    {
        $domainGroup->location_network = $this->locationDnsSettingService->getAll($domainGroup->domains()->first()->id);
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

        if (!$this->changeIrouteSetting($domainGroup, $request->domain_id, $request->edited_by)) {
            return 'iRouteError';
        }

        $result = $this->domainGroupRepository->createDomainToGroup($request, $domainGroup->id);
        return $result;
    }

    public function edit(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $domain = $domainGroup->domains()->first();
        if ($request->user_group_id != 1 && $request->user_group_id != $domain->user_group_id) {
            return false;
        }

        return $this->domainGroupRepository->update($request, $domainGroup->id);
    }

    public function destroy(int $domainGroupId)
    {
        return $this->domainGroupRepository->destroy($domainGroupId);
    }

    public function destroyByDomainId(DomainGroup $domainGroup, Domain $domain)
    {
        $domainCollection = $domainGroup->domains;

        if ($domainCollection->count() == 1) {
            return false;
        }
        return $this->domainGroupRepository->destroyByDomainId($domainGroup->id, $domain->id);
    }

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

    public function changeCdnDefault(DomainGroup $domainGroup, int $domainId, string $editedBy)
    {
        $domain = Domain::find($domainId);
        $getDomainCdnProviderId = $domainGroup->domains()->first()->cdns()->where('default', 1)->first()->cdn_provider_id;
        $targetCdn = $domain->cdns->where('cdn_provider_id', $getDomainCdnProviderId)->first();

        return $this->cdnService->changeDefaultToTrue($domain, $targetCdn, $editedBy);
    }

    public function changeIrouteSetting(DomainGroup $domainGroup, int $domainId, string $editedBy)
    {

        $originCdnSetting = $domainGroup->domains()->first()->cdns()->get();
        list($originIrouteSetting,$nonSettingCdn) = $this->getLocationSetting($originCdnSetting);

        if (empty($originIrouteSetting)) {
            return true; //如果 Group 內 cdn 沒有設定 iroute 就不做更改。
        }

        $targetDomain = Domain::find($domainId);
        $result = '';

        foreach ($originIrouteSetting as $iRouteSetting) {
            $targetCdn = $targetDomain->cdns()->where('cdn_provider_id', $iRouteSetting->cdn_provider_id)->first();
            $existLocationDnsSetting = $this->checkExist($targetDomain, $iRouteSetting->location_networks_id);

            $data = ['cdn_id' => $targetCdn->id,
                'edited_by' => $editedBy];

            if (!collect($existLocationDnsSetting)->isEmpty()) {
                $result = $this->locationDnsSettingService->updateSetting($data, $targetDomain, $targetCdn, $existLocationDnsSetting);
            } else {
                $result = $this->locationDnsSettingService->createSetting($data, $targetDomain, $targetCdn, $iRouteSetting->location);
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
                        'success' => false,
                        'message' => "error: no cdns where ['domain_id' =>  $domainId]"
                    ];
                    return true;
                }

                // 修改 location_dns_settings & DNSPOD
                // 查
                // $locationDnsSetting = LocationDnsSetting::all();
                $locationDnsSetting = LocationDnsSetting::where('location_networks_id', $locationNetwork->id)->whereIn('cdn_id', $scopeCdnIds)->first();
                // dd(is_null($locationDnsSetting));
                if (is_null($locationDnsSetting)) {
                    $returns[$k] = [
                        'domain' => $v,
                        'success' => false,
                        'message' => "error: no location_dns_settings where ['location_networks_id' => $locationNetwork->id] and where in ['cdn_id' => $scopeCdnIds]"
                    ];
                    return true;
                }
                // 改
                $targetDomain = Domain::find($domainId);
                $targetCdn = $this->cdnRepository->indexByWhere(['cdn_provider_id' => $cdnProviderId, 'domain_id' => $domainId])->first();
                $data = [
                    'cdn_id' => $targetCdn->id,
                    'edited_by' => $editedBy
                ];
                if (is_null($targetCdn)) {
                    $returns[$k] = [
                        'domain' => $v,
                        'success' => false,
                        'message' => "error: no cdn where ['cdn_provider_id' => $cdnProviderId, 'domain_id' => $domainId]"
                    ];
                    return true;
                }
                $result = $this->locationDnsSettingService->updateSetting($data, $targetDomain, $targetCdn, $locationDnsSetting);
                $returns[$k] = [
                    'domain' => $v,
                    'success' => true,
                    'message' => 'add dnspod & location_dns_settings result: ' . json_encode($result)
                ];
            });
            return $returns;
        } catch(Exception $e) {
            return $e;
        }
    }

    private function getLocationSetting(Collection $cdnSetting)
    {
        $targetIrouteSetting = [];
        $nonSettingCdn = [];

        foreach ($cdnSetting as $cdns) {
            $originLocationDnsSetting = $cdns->locationDnsSetting;
            if ($originLocationDnsSetting->isEmpty()) {
                $nonSettingCdnProviderId = $cdns->cdn_provider_id;
                array_push($nonSettingCdn, $nonSettingCdnProviderId);
                continue;
            }
            $originLocationDnsSetting[0]->cdn_provider_id = $cdns->cdn_provider_id;
            array_push($targetIrouteSetting, $originLocationDnsSetting[0]);
        }

        return [$targetIrouteSetting, $nonSettingCdn];
    }

    private function checkExist(Domain $domain, int $locationNetworkId)
    {
        $cdnId = $domain->cdns->pluck('id');
        return LocationDnsSetting::where('location_networks_id', $locationNetworkId)->whereIn('cdn_id', $cdnId)->first();
    }
}
