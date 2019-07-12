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
        
        $this->assertArrayHasKey('cdns',$data['data'][0]);
        $this->assertArrayHasKey('cdn_provider',$data['data'][0]);
        $this->assertArrayHasKey('location_dns_settings',$data['data'][0]);
        $this->assertArrayHasKey('domain_group',$data['data'][0]);
    }
}
