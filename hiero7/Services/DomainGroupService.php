<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\LocationDnsSetting;use Hiero7\Repositories\DomainGroupRepository;
use Hiero7\Services\CdnService;
use Hiero7\Services\LocationDnsSettingService;
use Illuminate\Database\Eloquent\Collection;

class DomainGroupService
{
    protected $domainGroupRepository;

    public function __construct(DomainGroupRepository $domainGroupRepository, CdnService $cdnService, LocationDnsSettingService $locationDnsSettingService)
    {
        $this->domainGroupRepository = $domainGroupRepository;
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

            $cdn = $domainModel->cdns()->where('default', 1)->first()->cdnProvider()->first();
            $item->setAttribute('default_cdn_name', $cdn->name);
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

        return $domainGroup;
    }

/**
 * Create Group function
 *
 * 先檢查 Domain 和 操作人的 user_group_id 是否相同，若 user_group_id ＝ 1 例外。
 * 若 user_group_id = 1 會依照欲加入的 Domain 的 user_group_id 給與新建立的 Group 相同 user_group_id。
 * @param array $request
 */
    public function create(array $request)
    {
        $domainModle = Domain::where('id', $request['domain_id'])->first();
        
        if($domainModle->cdns->isEmpty()){
            return 'NoneCdn';
        }

        $request['user_group_id'] == 1 ? $request['user_group_id'] = $domainModle->user_group_id : $request['user_group_id'];

        if ($request['user_group_id'] != $domainModle->user_group_id) {
            return 'differentGroup';
        }

        $result = $this->domainGroupRepository->create($request);

        if (!$result) {
            return 'exist';
        }

        return $result;
    }

    public function createDomainToGroup(array $request, DomainGroup $domainGroup)
    {
        $checkDomainCdnSetting = $this->compareDomainCdnSetting($domainGroup, $request['domain_id']);

        if (!$checkDomainCdnSetting) {
            return false;
        }

        if (!$this->changeCdnDefault($domainGroup, $request)) {
            return 'cdnError';
        }

        if (!$this->changeIrouteSetting($domainGroup, $request)) {
            return 'iRouteError';
        }

        $result = $this->domainGroupRepository->createDomainToGroup($request, $domainGroup->id);
        return $result;
    }

    public function edit(array $request, DomainGroup $domainGroup)
    {
        $domain = $domainGroup->domains()->first();
        if ($request['user_group_id'] != 1 && $request['user_group_id'] != $domain->user_group_id) {
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

    private function compareDomainCdnSetting(DomainGroup $domainGroup, $targetDomainId)
    {
        $controlDomain = $domainGroup->domains;
        $controlCdnProvider = $controlDomain[0]->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');
        $targetCdnProvider = Domain::find($targetDomainId)->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');

        $different = $controlCdnProvider->diff($targetCdnProvider);

        return !$different->isEmpty() ? false : true;
    }

    private function changeCdnDefault(DomainGroup $domainGroup, array $request)
    {
        $domain = Domain::find($request['domain_id']);
        $getDomainCdnProviderId = $domainGroup->domains()->first()->cdns()->where('default', 1)->first()->cdn_provider_id;
        $targetCdn = $domain->cdns->where('cdn_provider_id', $getDomainCdnProviderId)->first();

        return $this->cdnService->changeDefaultToTrue($domain, $targetCdn, $request['edited_by']);
    }

    private function changeIrouteSetting(DomainGroup $domainGroup, array $request)
    {

        $originCdnSetting = $domainGroup->domains()->first()->cdns()->get();
        $originIrouteSetting = $this->getLocationSetting($originCdnSetting);

        if (empty($originIrouteSetting)) {
            return true; //如果 cdn 沒有設定 iroute 就不做更改。
        }

        $targetDomain = Domain::find($request['domain_id']);
        $result = '';

        foreach ($originIrouteSetting as $iRouteSetting) {
            $targetCdn = $targetDomain->cdns()->where('cdn_provider_id', $iRouteSetting->cdn_provider_id)->first();
            $existLocationDnsSetting = $this->checkExist($targetDomain, $iRouteSetting->location_networks_id);

            $data = ['cdn_id' => $targetCdn->id,
                'edited_by' => $request['edited_by']];

            if (!collect($existLocationDnsSetting)->isEmpty()) {
                $result = $this->locationDnsSettingService->updateSetting($data, $targetDomain, $targetCdn, $existLocationDnsSetting);
            } else {
                $result = $this->locationDnsSettingService->createSetting($data, $targetDomain, $targetCdn, $iRouteSetting->location);
            }
        }

        return $result;
    }

    private function getLocationSetting(Collection $cdnSetting)
    {
        $targetIrouteSetting = [];

        foreach ($cdnSetting as $cdns) {
            $originLocationDnsSetting = $cdns->locationDnsSetting;
            if ($originLocationDnsSetting->isEmpty()) {
                continue;
            }
            $originLocationDnsSetting[0]->cdn_provider_id = $cdns->cdn_provider_id;
            array_push($targetIrouteSetting, $originLocationDnsSetting[0]);
        }
        return $targetIrouteSetting;
    }

    private function checkExist(Domain $domain, int $locationNetworkId)
    {
        $cdnId = $domain->cdns->pluck('id');
        return LocationDnsSetting::where('location_networks_id', $locationNetworkId)->whereIn('cdn_id', $cdnId)->first();
    }
}
