<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
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
        $locationNetwork = LocationNetwork::find(1);
        $locationNetwork->continent;
        $locationNetwork->country;
        $locationNetwork->network;
        $result->location_networks = $locationNetwork;
//        $result->location = 'All';
//        $result->isp = 'All';
//        $result->network_id = 2;
//        $result->continent = ['id' => 1, 'name' => 'africa'];
//        $result->country = ['id' => 2, 'name' => 'not china'];
//        $result->network = ['id' => 2, 'scheme_id' => '1', 'name' => '国外'];
//
        $result2 = new \stdClass();
        $result2->latency = 179;
        $locationNetwork2 = LocationNetwork::find(2);
        $locationNetwork2->continent;
        $locationNetwork2->country;
        $locationNetwork2->network;
        $result2->location_networks = $locationNetwork2;
//        $result2->location = 'All';
//        $result2->isp = 'All';
//        $result2->network_id = 1;
//        $result2->continent = ['id' => 4, 'name' => 'europe'];
//        $result2->country = ['id' => 2, 'name' => 'not china'];
//        $result2->network = ['id' => 2, 'scheme_id' => '1', 'name' => '国外'];

        $cdnProvider = CdnProvider::find(1);

        $scanned = collect([
            $result,
            $result2
        ]);

        return $this->response("", null, compact('cdnProvider', 'scanned'));
    }
}
