<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\LocationDnsSettingController;
use App\Http\Requests\LocationDnsSettingRequest as Request;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationNetwork;
use Hiero7\Services\DomainGroupService;
use Hiero7\Services\LocationDnsSettingService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery as m;
use Tests\TestCase;

class LocationDnsSettingTest extends TestCase
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

        $this->domain = (new Domain())->find(1);
        $this->locationNetwork = (new LocationNetwork())->find(1);
        $this->request = new Request;
        $this->addUuidforPayload()->setJwtTokenPayload(1, $this->jwtPayload);

        $this->mockLocationDnsSettingService = m::mock(LocationDnsSettingService::class);

        $this->controller = new LocationDnsSettingController(
            $this->mockLocationDnsSettingService,
            $this->domainGroupService
        );
    }

    public function service(DomainGroupService $domainGroupService)
    {
        $this->domainGroupService = $domainGroupService;
    }

    /** @test */
    public function editSetting_is_Success()
    {
        $this->setRequest([
            'cdn_provider_id' => 1,
        ]);

        $this->set_Mock_Method_DecideAction_output();

        $this->response = $this->controller->editSetting($this->request, $this->domain, $this->locationNetwork);

        $this->checkoutResponse();
    }

    /** @test */
    public function editSetting_HttpStatus_is_400()
    {
        $this->setRequest([
            'cdn_provider_id' => 1,
        ]);

        $this->set_Mock_Method_DecideAction_output('differentGroup');

        $this->response = $this->controller->editSetting($this->request, $this->domain, $this->locationNetwork);

        $this->checkoutResponse(400);
    }

    /** @test */
    public function editSetting_HttpStatus_is_409()
    {
        $this->setRequest([
            'cdn_provider_id' => 1,
        ]);

        $this->set_Mock_Method_DecideAction_output(false);

        $this->response = $this->controller->editSetting($this->request, $this->domain, $this->locationNetwork);

        $this->checkoutResponse(409);
    }

    private function setRequest(array $array = [])
    {
        $this->request->merge($array);
    }

    private function set_Mock_Method_DecideAction_output($output = true)
    {
        $this->mockLocationDnsSettingService
            ->shouldReceive('decideAction')
            ->andReturn($output);
    }
}
