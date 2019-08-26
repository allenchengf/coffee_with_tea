<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\ScanPlatform;
use Hiero7\Services\ScanProviderService;

class ScanProviderController extends Controller
{
    protected $scanProviderService;

    /**
     * NetworkService constructor.
     */
    public function __construct(ScanProviderService $scanProviderService)
    {
        $this->scanProviderService = $scanProviderService;
    }

    public function changeToBestCDNProviderByDomain(Domain $domain)
    {
        $result = $this->scanProviderService->changeToBestCDNProviderByDomain($domain);

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
    public function scannedData(ScanPlatform $scanPlatform,ScanProviderRequest $request)
    {
        $cdnProvider = CdnProvider::find($request->get('cdn_provider_id'));
        $cdnProviderUrl = $cdnProvider->url;
        $scanned = [];

        if(isset($cdnProviderUrl)){
            $scanned = $this->scanProviderService->getScannedData($scanPlatform, $cdnProvider->url);
        }
        return $this->response("", null, compact('cdnProvider', 'scanned'));
    }
}
