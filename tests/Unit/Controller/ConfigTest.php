<?php

namespace Tests\Unit\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Services\{ConfigService,DnsPodRecordSyncService};
use App\Http\Controllers\Api\v1\ConfigController;
use Illuminate\Http\Request;
use Hiero7\Models\{Domain,CdnProvider,DomainGroup};



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
        app()->call([$this, 'service']);
        $this->controller = new ConfigController($this->configService,$this->dnsPodRecordSyncService);
        $this->dnsPodRecordSyncService->shouldReceive('syncAndCheckRecords')->withAnyArgs()->andReturn([]);

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function service(ConfigService $configService,DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->configService = $configService;
        $this->dnsPodRecordSyncService = $this->initMock(DnsPodRecordSyncService::class);
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId(1)->setJwtTokenPayload(1,$this->jwtPayload);
    }
    
    public function testIndex()
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

    public function testImport()
    {
        $request = new Request;
        $domain = new Domain;
        $cdnProvider = new CdnProvider;
        $domainGroup = new DomainGroup;
        
        $request->merge([
            "domains" => [
                [
                  "id" => 1,
                  "user_group_id" => 1,
                  "name" => "yuan1.test.com",
                  "cname" => "yuan1testcom.1",
                  "label" => "yuan",
                  "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                  "cdns" => [
                    [
                      "id" => 3,
                      "domain_id" => 1,
                      "cdn_provider_id" => 1,
                      "provider_record_id" => 438651947,
                      "cname" => "test.cdn1.com",
                      "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                      "default" => true
                    ],[
                      "id" => 4,
                      "domain_id" => 1,
                      "cdn_provider_id" => 2,
                      "provider_record_id" => 0,
                      "cname" => "yuan1test.cdn2.com",
                      "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                      "default" => false,
                    ],[
                      "id" => 5,
                      "domain_id" => 1,
                      "cdn_provider_id" => 3,
                      "provider_record_id" => 0,
                      "cname" => "yuan1test.cdn3.com",
                      "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                      "default" => false
                    ],
                ],
                  "location_dns_settings" => [],
                ],
                [
                  "id" => 2,
                  "user_group_id" => 1,
                  "name" => "yuan2.test.com",
                  "cname" => "yuan2testcom.1",
                  "label" => "yuan",
                  "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                  "cdns" => [
                    [
                      "id" => 6,
                      "domain_id" => 2,
                      "cdn_provider_id" => 2,
                      "provider_record_id" => 438652135,
                      "cname" => "yuan2test.cdn2.com",
                      "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                      "default" => true,
                    ],
                ],
                  "location_dns_settings" => [],
                ],
            ],
              "cdnProviders" => [
                [
                  "id" => 1,
                  "name" => "yuanYuan",
                  "status" => true,
                  "user_group_id" => 1,
                  "ttl" => 428967,
                ],[
                  "id" => 2,
                  "name" => "Cloudflare",
                  "status" => true,
                  "user_group_id" => 1,
                  "ttl" => 470050,
                ],[
                  "id" => 3,
                  "name" => "CloudFront",
                  "status" => true,
                  "user_group_id" => 1,
                  "ttl" => 343530,
                ],[
                  "id" => 4,
                  "name" => "CloudFront",
                  "status" => true,
                  "user_group_id" => 1,
                  "ttl" => 12314,
                ]
                ],
              "domainGroups" => [
                [
                  "id" => 1,
                  "user_group_id" => 1,
                  "name" => "Group1",
                  "label" => "Label",
                  "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33",
                ]
                ],
              "edited_by" => "07e9f87f-457f-45d0-8652-6845a3961b33"
        ]);

        $result = $this->controller->import($request, $domain, $cdnProvider, $domainGroup);

        $data = json_decode($result->getContent(), true);

        $this->assertArrayHasKey('domains',$data['data']);
        $this->assertArrayHasKey('cdns',$data['data']);
        $this->assertArrayHasKey('locationDnsSetting',$data['data']);
        $this->assertArrayHasKey('cdnProvider',$data['data']);
        $this->assertArrayHasKey('domainGroup',$data['data']);
    }
}
