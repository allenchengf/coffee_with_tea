<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Services\ContinentService;
use Hiero7\Services\CountryService;
use Hiero7\Services\NetworkService;
use App\Http\Requests\NetworkRequest as Request;
use Hiero7\Services\SchemeService;

class NetworkController extends Controller
{
    protected $networkService;
    protected $continentService;
    protected $countryService;
    protected $schemeService;
    /**
     * NetworkController constructor.
     */
    public function __construct(NetworkService $networkService,ContinentService $continentService, CountryService $countryService, SchemeService $schemeService)
    {
        $this->networkService = $networkService;
        $this->continentService = $continentService;
        $this->countryService = $countryService;
        $this->schemeService = $schemeService;
    }

    public function index($schemeId)
    {
        $result = [];
        $schemeIdByName = $this->schemeService->getSchemeIdByName(env('SCHEME'));
        $data = $this->networkService->getNetworksById($schemeIdByName);

        $data->map(function ($item) use($schemeId) {
            if($item['locationNetwork']){
                $item['locationNetwork']->continent_name = $this->continentService->getContinentName($item['locationNetwork']->continent_id);
                $item['locationNetwork']->country_name = $this->countryService->getCountryName($item['locationNetwork']->country_id);
            }
        })->all();

        foreach ($data as $k => $v){
              if($v['scheme_id'] == $schemeId){
                  $result[] = $v;
              }
        }

        return $this->response("Success", null, $result);
    }
}
