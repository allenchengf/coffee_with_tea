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

    public function scannedData(ScanProviderRequest $request)
    {
        $result = new \stdClass();
        $result->latency = 178;
        $result->location = 'All';
        $result->isp = 'All';
        $result->network_id = 2;
        $result->continent = ['id' => 1, 'name' => 'africa'];
        $result->country = ['id' => 2, 'name' => 'not china'];
        $result->network = ['id' => 2, 'scheme_id' => '1', 'name' => '国外'];

        $result2 = new \stdClass();
        $result2->latency = 179;
        $result2->location = 'All';
        $result2->isp = 'All';
        $result2->network_id = 1;
        $result2->continent = ['id' => 4, 'name' => 'europe'];
        $result2->country = ['id' => 2, 'name' => 'not china'];
        $result2->network = ['id' => 2, 'scheme_id' => '1', 'name' => '国外'];

        $provider_name = 'Hiero7';

        $scanned = collect([
            $result,
            $result2
        ]);
        
        return $this->response("", null, compact('provider_name', 'scanned'));
    }
}
