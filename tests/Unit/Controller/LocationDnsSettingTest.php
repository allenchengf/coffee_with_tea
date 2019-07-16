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
        $response = $this->call('GET', $this->uri.'/1/iRouteCDN');
        $response->assertStatus(200);
    }

     public function testIndexByGroup()
    {
        $response = $this->call('GET', 'api/v1/iRouteCDN/lists');
        $response->assertStatus(200);
    }
}
