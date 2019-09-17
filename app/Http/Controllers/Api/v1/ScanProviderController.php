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
use Hiero7\Services\ScanProviderService;use Hiero7\Traits\JwtPayloadTrait;

class ScanProviderController extends Controller
{
    use JwtPayloadTrait;

    protected $scanProviderService;

    /**
     * NetworkService constructor.
     */
    public function __construct(ScanProviderService $scanProviderService)
    {
        $this->scanProviderService = $scanProviderService;
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
     * Select A Change To B Cdn Provider by IRoute
     *
     * @param ScanProviderRequest $request
     * @param LocationNetwork $locationNetworkId
     * @return ScanProviderController
     */
    public function changeCDNProviderByIRoute(ScanProviderRequest $request, LocationNetwork $locationNetworkId)
    {
        $oldCdnProviderId = $request->get('old_cdn_provider_id');
        $newCdnProviderId = $request->get('new_cdn_provider_id');

        $domains = $this->scanProviderService->changeCDNProviderByIRoute($locationNetworkId, $oldCdnProviderId, $newCdnProviderId);

        return $this->response('', null, $domains);
    }

    /**
     * @param ScanPlatform $scanPlatform
     * @param ScanProviderRequest $request
     * @return ScanProviderController
     */
    public function creatScannedData(ScanPlatform $scanPlatform, ScanProviderRequest $request)
    {
        $scanned = [];

        $cdnProvider = $this->initCdnProviderForScannedData($request);

        // cdn_provider: url未設定 / scannable 關閉狀態
        if (!$cdnProvider || !isset($cdnProvider->url) || $cdnProvider->scannable == 0) {
            return $this->setStatusCode(400)->response('', InputError::CHECK_CDN_PROVIDER_SETTING, []);
        }

        $scanned = $this->scanProviderService->creatScannedData($scanPlatform, $cdnProvider);
        // cdn_provider: url未設定 / scannable 關閉狀態
        if (empty($scanned)) {
            return $this->setStatusCode(400)->response('', InternalError::CHECK_DATA_AND_SCHEME_SETTING, []);
        }

        return $this->response("", null, compact('cdnProvider', 'scanned'));
    }

    /**
     * @param ScanPlatform $scanPlatform
     * @param ScanProviderRequest $request
     * @return ScanProviderController
     */
    public function indexScannedData(ScanPlatform $scanPlatform, ScanProviderRequest $request)
    {
        $scanned = [];

        $cdnProvider = $this->initCdnProviderForScannedData($request);

        $scanned = $this->scanProviderService->indexScannedData($scanPlatform, $cdnProvider);

        // `rename` & `only` scan_platform specific key
        $cdn_provider = &$cdnProvider;
        $scan_platform = collect($scanPlatform)->only(['id', 'name']);

        return $this->response("", null, compact('cdn_provider', 'scan_platform', 'scanned'));
    }

    private function initCdnProviderForScannedData($request)
    {
        return CdnProvider::where('id', $request->get('cdn_provider_id'))
            ->where('user_group_id', $this->getJWTUserGroupId())
            ->first();
    }
}
