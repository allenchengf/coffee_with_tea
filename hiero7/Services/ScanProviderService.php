<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Traits\JwtPayloadTrait;
use Illuminate\Support\Collection;

class ScanProviderService
{
    use JwtPayloadTrait;
    protected $locationDnsSettingRepository;
    protected $cdnService;
    protected $dnsPodRecordSyncService;

    /**
     * NetworkService constructor.
     */
    public function __construct(
        CdnService $cdnService,
        DnsPodRecordSyncService $dnsPodRecordSyncService,
        LocationDnsSettingRepository $locationDnsSettingRepository
    )
    {
        $this->cdnService = $cdnService;
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;

    }

    public function selectAchangeToBCdnProvider($fromCdnProviderId, $toCdnProviderId)
    {
        $cdnProviderIdList = [$fromCdnProviderId, $toCdnProviderId];

        $deleteCdnIdList = [];
        $tagertCdn = [];

        $domains = Domain::where('user_group_id', $this->getJWTUserGroupId())->with(array('cdnProvider' => function ($query) use ($cdnProviderIdList) {
            $query->whereIn('cdn_providers.id', $cdnProviderIdList);
        }))->get()->filter(function ($item) use (&$deleteCdnIdList, &$tagertCdn, $toCdnProviderId) {
            if (count($item->cdnProvider) == 2) {
                collect($item->cdnProvider)->map(function ($cdnProvider) use (&$deleteCdnIdList, &$tagertCdn, $toCdnProviderId) {

                    // 找出要刪除 Location Network 的 Cdn ID
                    $deleteCdnIdList[] = $cdnProvider->cdns->id;

                    //找出 要切換的目標 cdn
                    if ($cdnProvider->id == $toCdnProviderId && $cdnProvider->cdns->default == 0) {
                        $tagertCdn[] = $cdnProvider->cdns;
                    }
                });
                return true;
            }
        });

        $this->deleteLocationDnsSettingByIdList($deleteCdnIdList);
        $this->changeDefaultCdnByCdnList($tagertCdn);
        $this->syncReocordByDomains($domains);

        return $domains;
    }

    /**
     * 將全部的 Domain 切換至指定的 Cdn Provider 包含 Default
     *
     * @param int $cdnProviderId
     * @return mixed
     */
    public function changeCdnProviderById(int $cdnProviderId)
    {
        $tagertDefaultCdn = [];

        $domains = Domain::where('user_group_id', $this->getJWTUserGroupId())->with(array('cdnProvider' => function ($query) use ($cdnProviderId) {
            $query->whereIn('cdn_providers.id', [$cdnProviderId]);
        }))->get()->filter(function ($item) use (&$tagertDefaultCdn, $cdnProviderId) {
            if (count($item->cdnProvider) >= 1) {
                $item->locationDnsSettings()->delete();

                collect($item->cdnProvider)->map(function ($cdnProvider) use (&$tagertDefaultCdn, $cdnProviderId) {
                    //找出 要切換的目標 cdn
                    if ($cdnProvider->id == $cdnProviderId && $cdnProvider->cdns->default == 0) {
                        $tagertDefaultCdn[] = $cdnProvider->cdns;
                    }
                });
                return true;
            }
        });

        $this->changeDefaultCdnByCdnList($tagertDefaultCdn);
        $this->syncReocordByDomains($domains);

        return $domains;
    }

    /**
     * 刪除 Location Dns Setting 的資料 By Id List
     * @param array $idList
     */
    private function deleteLocationDnsSettingByIdList(array $idList)
    {
        collect($idList)->map(function ($id) {
            $this->locationDnsSettingRepository->deleteByCdnId($id);
        });
    }

    /**
     * Change To Default Cdn By Cdn List
     *
     * @param array $cdnIdList
     */
    private function changeDefaultCdnByCdnList(array $cdnIdList)
    {
        collect($cdnIdList)->map(function ($cdn) {
            $targetCdn = Cdn::find($cdn->id);
            $this->cdnService->changeDefaultToTrue($targetCdn->domain, $targetCdn, $this->getJWTUuid());
        });
    }

    /**
     * Sync Record By Domain List
     *
     * @param Collection $domains
     */
    private function syncReocordByDomains(Collection $domains)
    {
        $domains->map(function ($domain) {
            $this->dnsPodRecordSyncService->syncAndCheckRecords($domain);
        });
    }

}
