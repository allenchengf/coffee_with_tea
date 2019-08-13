<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Models\Domain;
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


    public function index()
    {
        $scanProvider = collect(config('scanProvider'))->keys();

        return $this->response("", null, $scanProvider);
    }


    /**
     * 選擇 A CDN Provider Change To B CDN Provider
     * @param ScanProviderRequest $request
     * @return array
     */
    public function selectAchangeToBCdnProvider(ScanProviderRequest $request)
    {
        $oldCdnProviderId = $request->get('old_cdn_provider_id');
        $newCdnProviderId = $request->get('new_cdn_provider_id');

        $this->scanProviderService->selectAchangeToBCdnProvider($oldCdnProviderId, $newCdnProviderId);

        return $this->response();

    }

    /**
     * Change All Domain To Target CDN Provider
     * @param ScanProviderRequest $request
     * @return array
     */
    public function changeToCdnProvider(ScanProviderRequest $request)
    {
        $cdnProviderId = $request->get('cdn_provider_id');

        $this->scanProviderService->changeCdnProviderById($cdnProviderId);

        return $this->response();
    }
}
