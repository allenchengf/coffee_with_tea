<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Traits\JwtPayloadTrait;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;

class ScanProviderService
{
    use JwtPayloadTrait;
    protected $locationDnsSettingRepository;
    protected $cdnService;
    protected $dnsPodRecordSyncService;
    const CURL_TIMEOUT = 60;

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

    public function getScannedData($scanPlatform, $cdnProviderUrl)
    {
        $data = [];
        $data['url'] =  $cdnProviderUrl;
        $data['wait'] = env('SCAN_SECOND');
        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->all();

        if (count($locationNetwork) >0){
            $crawlerData = $this->curlToCrawler($scanPlatform->url, $data);
        }

        return $this->mappingData($crawlerData);
    }

    protected function curlToCrawler($url, array $data = [])
    {
        return Curl::to($url)
            ->withData($data)
            ->withTimeout(self::CURL_TIMEOUT)
            ->asJson()
            ->post();
    }

    private function mappingData($crawlerData)
    {
        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->all();

        $result = collect($locationNetwork)->map(function ($item, $key) use($crawlerData){
            $result = new \stdClass();
            $result->latency = collect($crawlerData->results)->whereIn('nameEn', $item->mapping_value)->pluck('latency')->first();
            $location_networks =new \stdClass();
            $location_networks->id = $item->id;
            $location_networks->continent_id = $item->continent_id;
            $location_networks->country_id = $item->country_id;
            $location_networks->location = $item->location;
            $location_networks->isp = $item->isp;
            $location_networks->network_id = $item->network_id;
            $location_networks->continent = $item->continent;
            $location_networks->country = $item->country;
            $location_networks->network = $item->network;
            $result->location_networks = $location_networks;
            return $result;
        });

        return $result;
    }
}
