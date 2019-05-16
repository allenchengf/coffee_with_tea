<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Services\ContinentService;
use Hiero7\Services\CountryService;
use Hiero7\Services\NetworkService;
use App\Http\Requests\NetworkRequest as Request;

class NetworkController extends Controller
{
    protected $networkService;
    protected $continentService;
    protected $countryService;
    /**
     * NetworkController constructor.
     */
    public function __construct(NetworkService $networkService,ContinentService $continentService, CountryService $countryService)
    {
        $this->networkService = $networkService;
        $this->continentService = $continentService;
        $this->countryService = $countryService;
    }

    public function index($id)
    {
        $data = $this->networkService->getNetworksById($id);

        $data->map(function ($item) {
            if($item['locationNetwork']){
                $item['locationNetwork']->continent_name = $this->continentService->getContinentName($item['locationNetwork']->continent_id);
                $item['locationNetwork']->country_name = $this->countryService->getCountryName($item['locationNetwork']->country_id);
            }
            return $item;
        })->all();

        return $this->response("Success", null, $data);
    }
}
