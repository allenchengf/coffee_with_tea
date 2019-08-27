<?php

namespace Tests\Unit\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\v1\LocationDnsSettingController;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;

class LocationDnsSettingTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class]);
        $this->artisan('migrate');        
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainGroupTableSeeder');
        $this->seed('DomainGroupMappingTableSeeder');
        $this->uri = "/api/v1/domains";
        $this->login();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId(1)->setJwtTokenPayload(1,$this->jwtPayload);
    }

    public function testIndexByDomain()
    {
        $response = $this->call('GET', $this->uri.'/1/routing-rules');
        $response->assertStatus(200);
    }

    public function testIndexByGroup()
    {
        $response = $this->call('GET', 'api/v1/routing-rules/lists');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('domainGroup',$data['data']);
        $this->assertArrayHasKey('domains',$data['data']);

    }

    public function testIndexAll()
    {
        $response = $this->call('GET', 'api/v1/routing-rules/all');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('domainGroup',$data['data']);
        $this->assertArrayHasKey('location_network',$data['data']['domainGroup'][0]);
        $this->assertArrayHasKey('domains',$data['data']);
        $this->assertArrayHasKey('location_network',$data['data']['domains'][0]);
    }

    public function testIndexGroups()
    {
        $response = $this->call('GET', 'api/v1/routing-rules/groups');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        // 換頁
        $this->assertArrayHasKey('current_page',$data['data']);
        $this->assertArrayHasKey('last_page',$data['data']);
        $this->assertArrayHasKey('total',$data['data']);

        // 資料
        $this->assertArrayHasKey('domain_groups',$data['data']);
        $this->assertArrayHasKey('location_network',$data['data']['domain_groups'][0]);
    }

    public function testIndexDomains()
    {
        $response = $this->call('GET', 'api/v1/routing-rules/domains');
        $response->assertStatus(200);

        $data = json_decode($response->getContent(), true);

        // 換頁
        $this->assertArrayHasKey('current_page',$data['data']);
        $this->assertArrayHasKey('last_page',$data['data']);
        $this->assertArrayHasKey('total',$data['data']);

        // 資料
        $this->assertArrayHasKey('domains',$data['data']);
        $this->assertArrayHasKey('location_network',$data['data']['domains'][0]);
    }
}
