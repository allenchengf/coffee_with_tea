<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\NetworkController;
use App\Http\Requests\NetworkRequest as Request;
use Hiero7\Models\Network;
use Hiero7\Services\ContinentService;
use Hiero7\Services\CountryService;
use Hiero7\Services\NetworkService;
use Hiero7\Services\SchemeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class NetworkTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');

        app()->call([$this, 'service']);
        $this->network = new network();
        $this->controller = new NetworkController(
            $this->networkService,
            $this->continentService,
            $this->countryService,
            $this->schemeService
        );
    }

    public function service(
        NetworkService $networkService,
        ContinentService $continentService,
        CountryService $countryService,
        SchemeService $schemeService
    ) {
        $this->networkService = $networkService;
        $this->continentService = $continentService;
        $this->countryService = $countryService;
        $this->schemeService = $schemeService;
    }

    /**
     * @test
     */
    public function listNetwork()
    {
        $this->response = $this->controller->getList();
        $this->checkoutResponse();

        $this->assertArrayHasKey('continent', $this->responseArrayData['data'][0]['location_network']);
        $this->assertArrayHasKey('country', $this->responseArrayData['data'][0]['location_network']);
    }

    /**
     * @test
     */
    public function storeNetwork()
    {
        $request = new Request;
        $name = '線路類型';
        $request->merge([
            'scheme_id' => 1,
            'name' => $name,
        ]);

        $this->response = $this->controller->store($request, $this->network);
        $this->checkoutResponse();
        $this->assertEquals($name, $this->responseArrayData['data']['name']);
    }

    private function checkoutResponse(int $code = 200)
    {
        $this->assertEquals($code, $this->response->status());
        $this->responseArrayData = json_decode($this->response->getContent(), true);
    }
}
