<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\ScanLog;
use Hiero7\Repositories\LineRepository;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;
use Hiero7\Models\LocationNetwork;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Repositories\DomainRepository;

class ScanProviderService
{
    use JwtPayloadTrait;
    const CURL_TIMEOUT = 60;

    protected $locationDnsSettionService;
    private $locationNetwork = [];

    /**
     * NetworkService constructor.
     */
    public function __construct(LocationDnsSettingService $locationDnsSettingService)
    {
        $this->locationDnsSettionService = $locationDnsSettingService;
        app()->call([$this, 'getLine']);
    }

    /**
     * @param Domain $domain
     * @return array
     */
    public function changeToBestCDNProviderByDomain(Domain $domain): array
    {
        $lastScanLogs = app()->call([$this, 'getLastScanLog']);

        $result = [];

        $lastScanLogs->map(function ($region, $regionKey) use (&$result, $domain) {
            foreach ($region as $cdnProviderKey => $latency) {
                $actionResult = $this->locationDnsSettionService->decideAction($cdnProviderKey, $domain, $this->locationNetwork[$regionKey]);

                // 如果要切換的CDN Provider，在此 Domain 沒有設定，就換下一個一直切換到有為止
                if ($actionResult === 'differentGroup') {

                    continue;
                } else {
                    $result[] = [
                        'status' => $actionResult,
                        'location_network' => $this->locationNetwork[$regionKey]
                    ];
                    break;
                }
            }
        });

        return $result;
    }

    /**
     *  Get 最後一次掃瞄的結果
     *
     * @param ScanLog $scanLog
     * @return Collection
     */
    public function getLastScanLog(ScanLog $scanLog): Collection
    {
        $lastScanLogs = $scanLog::all();

        $lastScanLogs->map(function ($lastScanLog) use (&$regions) {
            $regions[$lastScanLog->location_network_id][$lastScanLog->cdn_provider_id] = $lastScanLog->latency;
        });

        // $regions['location_network_id']['cdn_provider_id'] = latency
        return collect($regions)->map(function ($region) {
            return collect($region)->sort();
        });
    }

    /**
     * 取得所有 Location Network
     *
     * @param LineRepository $line
     * @return array|Collection
     */
    public function getLine(LineRepository $line)
    {
        $lines = $line->getLinesById();
        $this->locationNetwork = collect($lines)->keyBy('id');
        return $this->locationNetwork;
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
    public function getDomainsByCDNProviderIdList(DomainRepository $domainRepository, array $cdnProviderIdList = []): Collection
    {
        return $domainRepository->getDomainsByCDNProviderList($cdnProviderIdList);
    }

    /**
     * @param $scanPlatform
     * @param $cdnProviderUrl
     * @return Collection
     */
    public function getScannedData($scanPlatform, $cdnProviderUrl)
    {
        $crawlerData = [];
        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->filter(function ($item) {
            return $item->network->scheme_id == env('SCHEME');
        });

        $data = [
            'url' => $cdnProviderUrl,
            'wait' => env('SCAN_SECOND'),
        ];


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
        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->filter(function ($item) {
            return $item->network->scheme_id == env('SCHEME');
        });

        $crawlerResults = isset($crawlerData->results) ? $crawlerData->results : [];

        $scanneds = collect($locationNetwork)->map(function ($item, $key) use ($crawlerResults) {

            $scanned = new \stdClass();

            $scanned->latency = collect($crawlerResults)->whereIn('nameEn', $item->mapping_value)->pluck('latency')->first() ?? null;

            $item->continent;
            $item->country;
            $item->network;

            $scanned->location_networks = $item;

            return $scanned;
        });

        return $scanneds;
    }
}


