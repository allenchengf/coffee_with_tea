<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\ScanPlatform;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Services\ScanProviderService;
use Hiero7\Traits\JwtPayloadTrait;

use Hiero7\Repositories\{ScanLogRepository, CdnProviderRepository, LineRepository};

class ScanProviderController extends Controller
{
    use JwtPayloadTrait;

    protected $scanProviderService;
    protected $scanLogRepository;
    protected $cdnProviderRepository;
    protected $lineRepository;

    /**
     * NetworkService constructor.
     */
    public function __construct(
        ScanProviderService $scanProviderService,
        ScanLogRepository $scanLogRepository,
        CdnProviderRepository $cdnProviderRepository,
        LineRepository $lineRepository
    )
    {
        $this->scanProviderService = $scanProviderService;
        $this->scanLogRepository = $scanLogRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
        $this->lineRepository = $lineRepository;
    }

    /**
     * ugid 權限所有 cdn_providers 之 scan_logs，與 regions 的 mapping
     *
     */
    public function indexScannedData()
    {
        $scanneds = [];
        $lastScanLogCreatedAt = date('Y-m-d H:i:s');
        $scanPlatform = null;

        // 權限
        $ugid = $this->getJWTUserGroupId();

        // 權限所有 cdn_providers
        $cdnProviders = $this->cdnProviderRepository->getCdnProvider($ugid);
        if (! $cdnProviders) {
            return $this->setStatusCode(400)->response('', InputError::UGID_WITHOUT_CDN_PROVIDER, []);
        }

        // 權限所有 (cdn_providers 的 scan_logs) 與 regions - mapping)
        // 最後 scan_log 的 created_at, scan_platform_id
        $lastScanLog = $this->scanLogRepository->showLatestLog();
        if ($lastScanLog) {
            $lastScanLogCreatedAt = $lastScanLog['created_at'];
            $scanPlatformModel = ScanPlatform::find($lastScanLog['scan_platform_id']);
        }

        // scanPlatform used: 17ce or chinaz
        $scanPlatform = collect($scanPlatformModel)->only(['id', 'name']);

        // all `networks` for `scheme` which is dnspod_free or dnspod_enterprise
        $regions = $this->lineRepository->getRegion();

        // mapping regions & scan_logs within ugid's cdnProviders
        $cdnProviders->each(function ($cdnProviderModel, $i) use (&$scanneds, &$lastScanLogCreatedAt, &$scanPlatformModel, &$regions) {

            // 抓 `scan_logs` where scanPlatform ＆ cdnProvider
            $scannedLogs = $this->scanProviderService->indexScannedData($scanPlatformModel, $cdnProviderModel);

            // mapping 啦
            $scanneds[$i]['cdnProvider'] = $cdnProviderModel;
            $scanneds[$i]['scannedAt'] = null;
            $scanneds[$i]['scanned'] = $regions->map(function ($region) use (&$scannedLogs, &$scanneds, &$i) {
                $scanned = collect($scannedLogs)->filter(function ($item) use (&$region) {
                    return $item->location_networks->network_id == $region->network->id;
                })->first();

                // 有 mapping 到
                if($scanned) {
                    $scanneds[$i]['scannedAt'] = $scanned->created_at;
                    return [
                        'latency' => $scanned->latency,
                        'created_at' => $scanned->created_at,
                        'location_networks' => $region
                    ];
                }
                
                // 沒 mapping 到
                return [
                    'latency' => null,
                    'created_at' => null,
                    'location_networks' => $region
                ];
            })->values();
        });

        return $this->response("", null, compact('scanPlatform', 'scanneds'));
    }


    /**
     * 根據最後一次掃瞄的結果
     * 依照 Region 的 latency 優到裂的順序
     * 對 Domain 切換 CDN Provider
     *
     * @param DomainRepository $domainRepository
     */
    public function changeDomainRegion(Domain $domain)
    {
        $result = $this->scanProviderService->changeDomainRegionByScanData($domain);

        return $this->response('', null, $result);
    }

    /**
     * 根據最後一次掃瞄的結果
     * 依照 Region 的 latency 優到裂的順序
     * 對 Group 切換 CDN Provider
     *
     * @param DomainRepository $domainRepository
     */
    public function changeDomainGroupRegion(DomainGroup $domainGroup)
    {
        $result = $this->scanProviderService->changeDomainGroupRegionByScanData($domainGroup);

        return $this->response('', null, $result);
    }

    /**
     * 根據最後一次掃瞄的結果
     * 依照 Region 的 latency 優到裂的順序
     * 對每個 Doamin & Group 切換 CDN Provider
     *
     * @param DomainRepository $domainRepository
     */
    public function changeRegion(DomainRepository $domainRepository)
    {
        $domains = $domainRepository->getDomainByUserGroup();

        $result = $this->scanProviderService->changeAllRegionByScanData($domains);

        return $this->response('', null, $result);
    }

    /**
     * @param ScanPlatform $scanPlatform
     * @param ScanProviderRequest $request
     * @return ScanProviderController
     */
    public function creatScannedData(ScanPlatform $scanPlatform, ScanProviderRequest $request)
    {
        $scanned = [];
        $scannedAt = date('Y-m-d H:i:s', $request->scanned_at);

        $cdnProvider = $this->initCdnProviderForScannedData($request);

        // cdn_provider: url未設定 / scannable 關閉狀態
        if (!$cdnProvider || !isset($cdnProvider->url) || $cdnProvider->scannable == 0) {
            return $this->setStatusCode(400)->response('', InputError::CHECK_CDN_PROVIDER_SETTING, []);
        }

        // cdn_provider: url未設定 / scannable 關閉狀態
        $scanned = $this->scanProviderService->creatScannedData($scanPlatform, $cdnProvider, $scannedAt);
        if (empty($scanned)) {
            return $this->setStatusCode(400)->response('', InternalError::CHECK_DATA_AND_SCHEME_SETTING, []);
        }

        return $this->response("", null, compact('cdnProvider', 'scannedAt', 'scanned'));
    }

    /**
     * @param ScanPlatform $scanPlatform
     * @param ScanProviderRequest $request
     * @return ScanProviderController
     */
    public function indexScannedDataByPlatform(ScanPlatform $scanPlatform, ScanProviderRequest $request)
    {
        $scanned = [];
        $scannedAt = null;

        $cdnProvider = $this->initCdnProviderForScannedData($request);

        $scanned = $this->scanProviderService->indexScannedData($scanPlatform, $cdnProvider);
        if ($scanned && ! $scanned->isEmpty()) {
            $scannedAt = $scanned->first()->created_at;
        }

        $scanPlatform = collect($scanPlatform)->only(['id', 'name']);

        return $this->response("", null, compact('cdnProvider', 'scanPlatform', 'scannedAt', 'scanned'));
    }

    private function initCdnProviderForScannedData($request)
    {
        return CdnProvider::where('id', $request->get('cdn_provider_id'))
            ->where('user_group_id', $this->getJWTUserGroupId())
            ->first();
    }
}
