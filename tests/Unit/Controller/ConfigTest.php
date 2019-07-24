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

    public function testImportDataFailed()
    {
        $request = new Request;
        $domain = new Domain;
        $cdnProvider = new CdnProvider;
        $domainGroup = new DomainGroup;
        
        $request->merge([
            "domains" => [[
                "id" => 1,
                "user_group_id" => "1",
                "name" => "hiero7.test1.com",
                "cname" => "hiero7test1com.1",
                "label" => null,
                "edited_by" => null,
                "cdns" =>  [ 
                  [
                      "id" => 1,
                      "domain_id" => "1",
                      "cdn_provider_id" => "1",
                      "provider_record_id" => "0",
                      "cname" => "speedlll.com",
                      "edited_by" => null,
                      "default" => true,
                  ],
                  [
                      "id" => 2,
                      "domain_id" => "1",
                      "cdn_provider_id" => "2",
                      "provider_record_id" => "0",
                      "cname" => "dnspod.com",
                      "edited_by" => null,
                      "default" => false,
                  ],[
                      "id" => 3,
                      "domain_id" => "1",
                      "cdn_provider_id" => "3",
                      "provider_record_id" => "0",
                      "cname" => "ZwWqcv.com",
                      "edited_by" => null,
                      "default" => false,
                ]
              ],
                "location_dns_settings" =>[ [
                    "id" => 1,
                    "provider_record_id" => "123456",
                    "location_networks_id" => "1",
                    "cdn_id" => "1",
                    "edited_by" => null,
                    "created_at" => "2019-07-24 09:43:55",
                    "updated_at" => "2019-07-24 09:43:55",
                    "domain_id" => "1",
                  ]
                ],
              ],[
                "id" => 2,
                "user_group_id" => "1",
                "name" => "hiero7.test2.com",
                "cname" => "hiero7test2com.1",
                "label" => null,
                "edited_by" => null,
                "cdns" => [ [
                    "id" => 4,
                    "domain_id" => "2",
                    "cdn_provider_id" => "1",
                    "provider_record_id" => "0",
                    "cname" => "speedlll.com",
                    "edited_by" => null,
                    "default" => true,
                  ], [
                      "id" => 5,
                      "domain_id" => "2",
                      "cdn_provider_id" => "2",
                      "provider_record_id" => "0",
                      "cname" => "dnspod.com",
                      "edited_by" => null,
                      "default" => false,
                  ], [
                      "id" => 6,
                      "domain_id" => "2",
                      "cdn_provider_id" => "3",
                      "provider_record_id" => "0",
                      "cname" => "KYWDn5.com",
                      "edited_by" => null,
                      "default" => false,
                    ]
                ],
                "location_dns_settings" => [[
                    "id" => 2,
                    "provider_record_id" => "456123",
                    "location_networks_id" => "2",
                    "cdn_id" => "5",
                    "edited_by" => null,
                    "created_at" => "2019-07-24 09:43:55",
                    "updated_at" => "2019-07-24 09:43:55",
                    "domain_id" => "2",
                  ]
                ],
              ],
            ],
            "cdnProviders" => [
              [
                "id" => 1,
                "name" => "Hiero7",
                "status" => 'active',
                "user_group_id" => 1,
                "ttl" => "85125",
              ], [
                  "id" => 2,
                  "name" => "Cloudflare",
                  "status" => 'active',
                  "user_group_id" => 1,
                  "ttl" => "312204",
              ], [
                  "id" => 3,
                  "name" => "CloudFront",
                  "status" => 'active',
                  "user_group_id" => 1,
                  "ttl" => "136318",
                ]
            ],
            "domainGroups" => [ [
                "id" => 1,
                "user_group_id" => "1",
                "name" => "Group1",
                "label" => "This is Group1",
                "edited_by" => null,
                "mapping" => [ [
                    "id" => 1,
                    "domain_id" => "1",
                    "domain_group_id" => "1",
                  ]
                ]
              ]
            ]
        ]);

        try{
          $this->controller->import($request, $domain, $cdnProvider, $domainGroup);
        } catch (\Exception $e) {
          $result = $e->getMessage();
        }

        // $data = json_decode($result->getContent(), true);
        $this->assertEquals('Import Relational Data Have Some Problem.',$result);
        // $this->assertArrayHasKey('message',$data);
        // $this->assertEquals($data['message'], 'Success');
        // $this->assertArrayHasKey('errorCode',$data);
        // $this->assertArrayHasKey('data',$data);
    }
}
