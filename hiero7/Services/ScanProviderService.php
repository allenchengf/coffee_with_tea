<?php

namespace Hiero7\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\ScanLog;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\ScanLogRepository;
use Hiero7\Traits\JwtPayloadTrait;
use Illuminate\Support\Collection;
use Ixudra\Curl\Facades\Curl;

class ScanProviderService
{
    use JwtPayloadTrait;

    const CURL_TIMEOUT = 60;

    protected $locationDnsSettionService;

    protected $scanLogRepository;

    private $lastScanLog = [];

    private $locationNetworks = [];

    private $cdnProvider;
    /**
     * NetworkService constructor.
     */
    public function __construct(
        LocationDnsSettingService $locationDnsSettingService,
        ScanLogRepository $scanLogRepository
    ) {
        $this->locationDnsSettionService = $locationDnsSettingService;

        $this->scanLogRepository = $scanLogRepository;

        $this->lastScanLog = collect([]);
    }

    public function changeDomainGroupRegionByScanData(DomainGroup $domainGroup)
    {
        app()->call([$this, 'getLine']);

        $this->getLastScanLog();

        return $domainGroup->domains->map(function ($domain) {
            $result = $this->autoChangeRegionByScanData($domain);

            unset($domain->cdnProvider);

            return [
                "domain" => $domain,
                "result" => $result,
            ];
        });
    }

    /**
     * 根據檢測結果切換 Domain 的 Region
     *
     * @param Domain $domain
     * @return array
     */
    public function changeDomainRegionByScanData(Domain $domain): array
    {
        app()->call([$this, 'getLine']);

        $this->getLastScanLog();

        return $this->autoChangeRegionByScanData($domain);
    }

    /**
     * 根據最後一次掃瞄的結果
     * 依照 Region 的 latency 優到裂的順序
     * 對每個 Doamin & Group 切換 CDN Provider
     *
     * @param Collection $domains
     * @return Collection
     */
    public function changeAllRegionByScanData(Collection $domains): Collection
    {
        app()->call([$this, 'getLine']);

        $this->getLastScanLog();

        return $domains->map(function ($domain) {
            return [$domain->name => $this->autoChangeRegionByScanData($domain)];
        });
    }

    private function autoChangeRegionByScanData(Domain $domain)
    {
        // 如果 domain 沒有設定 CDN Provider 直接離開
        if ($domain->cdnProvider->isEmpty()) {
            return [];
        }

        $result = [];

        $this->LastScanLog->map(function ($region, $regionKey) use (&$result, $domain) {
            foreach ($region as $cdnProviderKey => $latency) {
                $actionResult = $this->locationDnsSettionService->decideAction($cdnProviderKey, $domain, $this->locationNetworks[$regionKey]);

                // 如果要切換的 CDN Provider，此 Domain 沒有設定 CDN Provider，
                // 換到下一個，一直切換到有為止
                if ($actionResult === 'differentGroup') {
                    continue;
                } else {
                    $result[] = [
                        'status' => $actionResult,
                        'location_network' => $this->locationNetworks[$regionKey],
                        'cdn_provider' => $this->cdnProvider[$cdnProviderKey],
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
     *  並且排出各個 CDN Provider 在各個線路下的優良順序
     *
     * @param ScanLog $scanLog
     * @return void
     *
     * example:
     * location_network_id->cdn_provider_id = latency
     *
     * {
     *      "2": {
     *          "3": "30",
     *          "1": "100",
     *          "2": "200"
     *      },
     *      "3": {
     *          "2": "200",
     *          "3": "300",
     *          "1": "400"
     *      }
     *  }
     */
    public function getLastScanLog()
    {
        $regions = [];

        app()->call([$this, 'getCdnProvider']);

        //取得最後一次 Scan 的結果
        $lastScanLogs = $this->scanLogRepository->indexEarlierLogs();

        $lastScanLogs->map(function ($lastScanLog) use (&$regions) {

            // 1000 > latency > 0
            if (isset($this->cdnProvider[$lastScanLog->cdn_provider_id]) && 1000 > $lastScanLog->latency && $lastScanLog->latency) {
                // $regions['location_network_id']['cdn_provider_id'] = latency
                $regions[$lastScanLog->location_network_id][$lastScanLog->cdn_provider_id] = $lastScanLog->latency;
            }
        });

        $this->LastScanLog = collect($regions)->map(function ($region) {
            // 根據 Latency 排序由小排到最大
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
        $this->locationNetworks = collect($line->getLinesById())->keyBy('id');
    }

    public function getCdnProvider(CdnProviderRepository $cdnProviderRepository)
    {
        $this->cdnProvider = $cdnProviderRepository->getStatusIsActive()->keyBy('id');
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
        if (!$scanLog) {
            return [];
        }

        // 處理 Query Data Output 格式
        $locationNetworkIdCollection = collect(explode(',', $scanLog->location_network_id));
        $latencyArray = explode(',', $scanLog->latency);
        $createdAt = $scanLog->created_at->format('Y-m-d H:i:s');

        $scanneds = $locationNetworkIdCollection->map(function ($locationNetworkId, $idx) use (&$latencyArray, &$createdAt) {
            $scanned = new \stdClass();

            // latency
            $scanned->latency = is_numeric($latencyArray[$idx]) ? (int) $latencyArray[$idx] : null;

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
    public function mappingData($crawlerData)
    {
        $locationNetwork = LocationNetwork::whereNotNull('mapping_value')->get()->filter(function ($item) {
            return $item->network->scheme_id == env('SCHEME');
        });

        $crawlerResults = collect(isset($crawlerData->results) ? $crawlerData->results : []);

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
