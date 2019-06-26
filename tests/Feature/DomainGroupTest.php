<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use Hiero7\Services\CdnService; 

class DomainGroupTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class]);
        Artisan::call('migrate');
        Artisan::call('db:seed');
        $this->seed('LocationDnsSettingSeeder');
        $this->uri = "/api/v1/groups";
        $this->login();
        $this->cdnService = $this->initMock(CdnService::class);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId(random_int(1, 5))->setJwtTokenPayload(random_int(1, 5),
            $this->jwtPayload);
    }

    public function testIndex()
    {
        $response = $this->call('GET', $this->uri);
        $response->assertStatus(200);
    }

    public function testCreate()
    {
        $body =[
            "name"=> "Group3",
            "domain_id"=>"3",
            "label"=> "LabelForGroup3"
        ];
        $response = $this->call('POST', $this->uri ,$body);
        $response->assertStatus(200);
    }

    public function testEdit()
    {
        $body =[
            "name"=> "Group3",
            "default_cdn_provider_id"=>2,
            "label"=> "LabelForGroup3"
        ];
        $response = $this->call('PUT', $this->uri.'/1' ,$body);
        $response->assertStatus(200);
    }

    public function testDestroy()
    {
        $response = $this->call('DELETE', $this->uri.'/1');
        $response->assertStatus(200);
    }

    public function testIndexByDomainGroupId()
    {
        $response = $this->call('GET', $this->uri.'/1');
        $response->assertStatus(200);
    }

    public function testDestroyByDomainId()
    {
        $body =[
            "domain_id"=> 2
        ];
        $response = $this->call('DELETE', $this->uri.'/1/domain/1',$body);
        $response->assertStatus(200);
    }

    public function testChangeDefaultCdn()
    {
        $this->cdnService->shouldReceive('changeDefaultToTrue')->withAnyArgs()->andReturn(true);
        $body =[
            "cdn_provider_id"=> 2
        ];
        $response = $this->call('PUT', $this->uri.'/1/defaultCdn',$body);
        $response->assertStatus(200);
    }

    public function testIndexGroupIroute()
    {
        $response = $this->call('GET', $this->uri.'/1/iRoute');
        $response->assertStatus(200);
    }

}
