<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\ScanLog;
use Hiero7\Repositories\LineRepository;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;
use Hiero7\Models\LocationNetwork;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Repositories\{DomainRepository, ScanLogRepository};

class ScanProviderService
{
    use JwtPayloadTrait;
    const CURL_TIMEOUT = 60;

    protected $locationDnsSettionService;
    private $locationNetwork = [];
    protected $scanLogRepository;

    /**
     * NetworkService constructor.
     */
    public function __construct(
        LocationDnsSettingService $locationDnsSettingService,
        ScanLogRepository $scanLogRepository
    )
    {
        $this->locationDnsSettionService = $locationDnsSettingService;
        $this->scanLogRepository = $scanLogRepository;
    }

    /**
     * 根據檢測結果切換 Domain Region
     *
     * @param Domain $domain
     * @return array
     */
    public function changeDomainRegionByScanData(Domain $domain): array
    {
        $lastScanLogs = app()->call([$this, 'getLastScanLog']);
        app()->call([$this, 'getLine']);

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
        $regions = [];
        
        $lastScanLogs = $this->scanLogRepository->indexEarlierLogs();

        $lastScanLogs->map(function ($lastScanLog) use (&$regions) {
            if ($lastScanLog->latency && $lastScanLog->latency < 1000) {
                $regions[$lastScanLog->location_network_id][$lastScanLog->cdn_provider_id] = $lastScanLog->latency;
            }
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
     * @return void
     */
    public function getLine(LineRepository $line)
    {
        $lines = $line->getLinesById();
        $this->locationNetwork = collect($lines)->keyBy('id');
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
     * @param $cdnProvider
     * @return Collection
     */
    public function indexScannedData($scanPlatform, $cdnProvider)
    {
        $scanneds = [];

        // DB Query
        $scanLog = $this->scanLogRepository->indexLatestLogs($cdnProvider->id, $scanPlatform->id);

        // 處理 Query Data Output 格式
        $locationNetworkIdCollection = collect( explode(',', $scanLog->location_network_id) );
        $latencyArray = explode(',', $scanLog->latency);
        $createdAt = $scanLog->created_at->format('Y-m-d H:i:s');

        $scanneds = $locationNetworkIdCollection->map(function ($locationNetworkId, $idx) use (&$latencyArray, &$createdAt) {
            $scanned = new \stdClass();

            // latency
            $scanned->latency = (int)$latencyArray[$idx];

            // created_at
            $scanned->created_at = $createdAt;
            
            // ORM: 與 location_networks 表相關
            $locationNetworkModel = LocationNetwork::find($locationNetworkId);
            $locationNetworkModel->continent;
            $locationNetworkModel->country;
            $locationNetworkModel->network;
            $scanned->location_networks = $locationNetworkModel;
            
            return $scanned;
        });
        
        return $scanneds;
    }

    /**
     * @param $scanPlatform
     * @param $cdnProvider
     * @return Collection
     */
    public function creatScannedData($scanPlatform, $cdnProvider)
    {
        $crawlerData = [];
        $scanneds = [];
        $created_at = date('Y-m-d H:i:s');

        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->filter(function ($item) {
            return $item->network->scheme_id == env('SCHEME');
        });

        $data = [
            'url' => $cdnProvider->url,
            'wait' => env('SCAN_SECOND'),
        ];

        if (count($locationNetwork) > 0) {
            $crawlerData = $this->curlToCrawler($scanPlatform->url, $data);
            
            $scanneds = $this->mappingData($crawlerData);
            $this->create($scanneds, $cdnProvider->id, $scanPlatform->id, $created_at);
        }

        return $scanneds;
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

        $crawlerResults = collect( isset($crawlerData->results) ? $crawlerData->results : [] );

        $scanneds = $locationNetwork->map(function ($item, $key) use ($crawlerResults) {
            $scanned = new \stdClass();

            $scanned->latency = $crawlerResults->whereIn('nameEn', $item->mapping_value)->pluck('latency')->first() ?? null;

            $item->continent;
            $item->country;
            $item->network;

            $scanned->location_networks = $item;

            return $scanned;
        });

        return $scanneds;
    }

    /**
     * @param $scanneds
     * @param $cdnProviderId
     * @param $scanPlatformId
     */
    private function create($scanneds, $cdnProviderId, $scanPlatformId, $created_at)
    {
        $edited_by = $this->getJWTUuid();
            
        $scanneds->each(function ($scanned) use (&$scanPlatformId, &$cdnProviderId, &$edited_by, &$created_at) {
            $fillable = [
                'cdn_provider_id' => $cdnProviderId,
                'scan_platform_id' => $scanPlatformId,
                'location_network_id' => $scanned->location_networks->id,
                'latency' => $scanned->latency,
                'edited_by' => $edited_by,
                'created_at' => $created_at,
            ];
            $this->scanLogRepository->create($fillable);
        });
    }
}


