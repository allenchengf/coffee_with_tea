<?php

namespace Tests\Unit\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConfigTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->artisan('migrate');        
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainGroupTableSeeder');
        $this->seed('DomainGroupMappingTableSeeder');
        $this->uri = "/api/v1/config";
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
        $response = $this->call('GET', $this->uri);
        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('domains',$data['data']);
        $this->assertArrayHasKey('cdns',$data['data']['domains'][0]);
        $this->assertArrayHasKey('location_dns_settings',$data['data']['domains'][0]);
        $this->assertArrayHasKey('cdnProviders',$data['data']);
        $this->assertArrayHasKey('domainGroups',$data['data']);
    }
}
