<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Models\{CdnProvider, Domain, LocationNetwork, ScanPlatform};
use Hiero7\Services\ScanProviderService;
use Hiero7\Enums\InputError;
use Hiero7\Traits\JwtPayloadTrait;

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

    public function changeDomainRegionByScanData(Domain $domain)
    {
        $result = $this->scanProviderService->changeDomainRegionByScanData($domain);

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

        if(isset($cdnProvider->url)){
            $scanned = $this->scanProviderService->creatScannedData($scanPlatform, $cdnProvider);
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
                            ->where('scannable', '>', 0)
                            ->first();
    }
}
