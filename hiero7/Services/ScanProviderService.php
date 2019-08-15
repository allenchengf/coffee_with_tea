<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;
use Hiero7\Models\LocationNetwork;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Repositories\DomainRepository;

class ScanProviderService
{
    use JwtPayloadTrait;
    protected $locationDnsSettingRepository;
    protected $cdnService;
    protected $dnsPodRecordSyncService;
    const CURL_TIMEOUT = 60;


    protected $locationDnsSettionService;

    /**
     * NetworkService constructor.
     */
    public function __construct(LocationDnsSettingService $locationDnsSettingService)
    {
        $this->locationDnsSettionService = $locationDnsSettingService;
    }

    /**
     *  Select A Change To B Cdn Provider by IRoute
     *
     * @param LocationNetwork $locationNetwork
     * @param int $fromCdnProviderId
     * @param int $toCdnProviderId
     * @return array
     */
    public function changeCDNProviderByIRoute(LocationNetwork $locationNetwork, int $fromCdnProviderId, int $toCdnProviderId): array
    {
        $domainAction = [];

        $domains = app()->call([$this, 'getDomainsByCDNProviderIdList'], [
            'cdnProviderIdList' => [$fromCdnProviderId, $toCdnProviderId],
        ]);

        $domains->map(function (Domain $domain) use ($locationNetwork, $toCdnProviderId, &$domainAction) {
            $domainAction[] = [
                'domain' => $domain->only('id', 'user_group_id', 'name', 'cname', 'label'),
                'action' => $this->locationDnsSettionService->decideAction($toCdnProviderId, $domain, $locationNetwork)
            ];
        });

        return $domainAction;
    }

    /**
     * Get Domains By CDN Provider Id List
     *
     * @param DomainRepository $domainRepository
     * @param array $cdnProviderIdList
     * @return Collection
     */
    public function getDomainsByCDNProviderIdList(
        DomainRepository $domainRepository,
        $cdnProviderIdList = []
    ): Collection {
        return $domainRepository->getDomainsByCDNProviderList($cdnProviderIdList);
    }

    /**
     * @param $scanPlatform
     * @param $cdnProviderUrl
     * @return Collection
     */
    public function getScannedData($scanPlatform, $cdnProviderUrl)
    {
        $data = [];
        $data['url'] = $cdnProviderUrl;
        $data['wait'] = env('SCAN_SECOND');

        $locationNetwork = LocationNetwork::all()->filter(function ($item) {
            return $item->network->scheme_id ==env('SCHEME');
        });

        if (count($locationNetwork) > 0) {
            $crawlerData = $this->curlToCrawler($scanPlatform->url, $data);
        }

        return $this->mappingData($crawlerData);
    }

    /**
     * @param $url
     * @param array $data
     * @return mixed
     */
    protected function curlToCrawler($url, array $data = [])
    {
        return Curl::to($url)
            ->withData($data)
            ->withTimeout(self::CURL_TIMEOUT)
            ->asJson()
            ->post();
    }

    /**
     * @param $crawlerData
     * @return Collection
     */
    private function mappingData($crawlerData)
    {
        $locationNetwork = LocationNetwork::all()->filter(function ($item) {
            return $item->network->scheme_id ==env('SCHEME');
        });

        $result = collect($locationNetwork)->map(function ($item, $key) use ($crawlerData) {
            $result = new \stdClass();
            $result->latency = collect($crawlerData->results)->whereIn('nameEn',
                $item->mapping_value)->pluck('latency')->first();
            $location_networks = new \stdClass();
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
