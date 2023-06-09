<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\CheckDnsPod;
use App\Http\Middleware\TokenCheck;
use Hiero7\Services\CdnService; 


class DomainGroupTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class, CheckDnsPod::class]);
        $this->artisan('migrate');        
        $this->seed();
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainGroupTableSeeder');
        $this->seed('DomainGroupMappingTableSeeder');
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
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
        $this->addUuidforPayload()->addUserGroupId(1)->setJwtTokenPayload(1,$this->jwtPayload);
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
            "domain_id"=>4,
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

    public function testDestroyLastDomainByDomainId()
    {
        $response = $this->call('DELETE', $this->uri.'/1/domain/2'); 
        $response->assertStatus(400);
    }

    public function testChangeDefaultCdn()
    {
        $this->cdnService->shouldReceive('changeDefaultToTrue')->withAnyArgs()->andReturn(true);
        $body =[
            "cdn_provider_id"=> 3
        ];
        $response = $this->call('PUT', $this->uri.'/1/defaultCdn',$body);
        $response->assertStatus(200);
    }

    public function testIndexGroupIroute()
    {
        $response = $this->call('GET', $this->uri.'/1/routing-rules');
        $response->assertStatus(200);
    }

}
