<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\v1\DnsPodRecordSyncController;
use App\Http\Requests\DnsPodRecordSyncRequest as Request;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Services\DnsProviderService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery as m;
use Tests\TestCase;

class DnsPodRecordSyncTest extends TestCase
{
    use DatabaseMigrations;

    protected $mockDnsProviderService, $domain, $jwtPayload = [];

    protected function setUp()
    {
        parent::setUp();

        $this->seed();
        $this->domain = new domain();
        $this->mockDnsProviderService = m::mock(DnsProviderService::class);

        app()->call([$this, 'repository']);

        $this->mockDnsPodRecordSyncService = new DnsPodRecordSyncService($this->mockDnsProviderService, $this->domainRepository);

        $this->controller = new DnsPodRecordSyncController($this->mockDnsPodRecordSyncService);

        $this->setDomainData();
        $this->setCdnProviderData();
        $this->setCdnData();
        $this->setLocationDnsSettingData();
    }

    public function repository(DomainRepository $domainRepository)
    {
        $this->domainRepository = $domainRepository;
    }

    /**
     * @test
     */
    public function index()
    {
        $expectedCount = 8;

        $response = $this->controller->index($this->domain);

        $data = $this->checkStatusAndReturnData($response);

        $this->assertCount($expectedCount, $data['data']);
    }

    /**
     * @test
     */
    public function getDomain()
    {
        $expectedCount = 4;

        $domain = $this->domain->find(1);

        $response = $this->controller->getDomain($domain);

        $data = $this->checkStatusAndReturnData($response);

        $this->assertCount($expectedCount, $data['data']);
    }

    /**
     * @test
     */
    public function checkDataDiff()
    {
        $name = 'leo1.com';

        $request = new Request;

        $request->merge(compact('name'));

        $this->muckGetDiffRecord();

        $this->mockCheckAPIOutput();

        $response = $this->controller->checkDataDiff($request, $this->domain);

        $data = $this->checkStatusAndReturnData($response);
    }

    private function mockCheckAPIOutput($check = true)
    {
        $this->mockDnsProviderService
            ->shouldReceive('checkAPIOutput')
            ->andReturn($check);
    }

    private function setDomainData()
    {
        $data = [
            [
                "id" => 1,
                "user_group_id" => 1,
                "name" => "leo1.com",
                "cname" => "leo1com.1",
                "label" => null,
            ], [
                "id" => 2,
                "user_group_id" => 1,
                "name" => "leo2.com",
                "cname" => "leo2com.1",
                "label" => null,
            ],
        ];

        foreach ($data as $key => $value) {
            $this->domain->insert($value);
        }
    }

    private function setCdnProviderData()
    {
        $data = [
            [
                "id" => 1,
                "ttl" => 485866,
            ], [
                "id" => 2,
                "ttl" => 401926,
            ], [
                "id" => 3,
                "ttl" => 54323,
            ],
        ];

        foreach ($data as $key => $value) {
            CdnProvider::find($value['id'])->
                update(['ttl' => $value['ttl']]);
        }
    }

    private function setCdnData()
    {
        $data = [
            [
                "id" => 1,
                "domain_id" => 1,
                "cdn_provider_id" => 1,
                "provider_record_id" => 437764331,
                "cname" => "hiero7.leo1.com",
                "default" => 1,
            ], [
                "id" => 2,
                "domain_id" => 1,
                "cdn_provider_id" => 2,
                "provider_record_id" => 0,
                "cname" => "cloudflare.leo1.com",
                "default" => 0,
            ], [
                "id" => 3,
                "domain_id" => 1,
                "cdn_provider_id" => 3,
                "provider_record_id" => 0,
                "cname" => "cloudFront.leo1.com",
                "default" => 0,
            ], [
                "id" => 4,
                "domain_id" => 2,
                "cdn_provider_id" => 1,
                "provider_record_id" => 437764101,
                "cname" => "hiero7.leo2.com",
                "default" => 1,
            ], [
                "id" => 5,
                "domain_id" => 2,
                "cdn_provider_id" => 2,
                "provider_record_id" => 0,
                "cname" => "cloudflare.leo2.com",
                "default" => 0,
            ], [
                "id" => 6,
                "domain_id" => 2,
                "cdn_provider_id" => 3,
                "provider_record_id" => 0,
                "cname" => "cloudFront.leo2.com",
                "default" => 0,
            ],
        ];
        foreach ($data as $key => $value) {
            Cdn::insert($value);
        }
    }

    private function setLocationDnsSettingData()
    {
        $data = [
            [
                "id" => 1,
                "provider_record_id" => 437764333,
                "location_networks_id" => 1,
                "cdn_id" => 1,
            ], [
                "id" => 2,
                "provider_record_id" => 437764335,
                "location_networks_id" => 2,
                "cdn_id" => 2,
            ], [
                "id" => 3,
                "provider_record_id" => 437764337,
                "location_networks_id" => 4,
                "cdn_id" => 2,
            ], [
                "id" => 4,
                "provider_record_id" => 437764109,
                "location_networks_id" => 1,
                "cdn_id" => 6,
            ], [
                "id" => 5,
                "provider_record_id" => 437764107,
                "location_networks_id" => 2,
                "cdn_id" => 4,
            ], [
                "id" => 6,
                "provider_record_id" => 437764116,
                "location_networks_id" => 4,
                "cdn_id" => 6,
            ],
        ];

        foreach ($data as $key => $value) {
            LocationDnsSetting::insert($value);
        }
    }

    private function checkStatusAndReturnData($response, int $statusCode = 200)
    {
        $this->assertEquals(200, $response->status());

        return json_decode($response->getContent(), true);
    }

    private function muckGetDiffRecord()
    {
        $this->mockDnsProviderService
            ->shouldReceive('getDiffRecord')
            ->andReturn([
                'message' => 'Success',
                'errorCode' => null,
                'data' => [
                    'different' => [
                        [
                            'id' => 438034966,
                            'ttl' => 401926,
                            'value' => 'cloudflare.leo1.com',
                            'enabled' => true,
                            'name' => 'leo1com.1',
                            'line' => '联通',
                            'hash' => '886f781f55e22b2c8c27877e5ad589c57236b2e6',
                        ],
                    ],
                    'create' => [
                        [
                            'id' => 438654702,
                            'ttl' => 401926,
                            'value' => 'cloudflare.leo1.com',
                            'enabled' => true,
                            'name' => 'leo1com.1',
                            'line' => '国内',
                            'hash' => '65bbdafe9415a58f1f09ab5ae59da01b6bbdf4ee',
                        ],
                    ],
                    'delete' => [
                        [
                            'id' => 438755246,
                            'ttl' => 600,
                            'value' => 'leo.123.com',
                            'enabled' => true,
                            'name' => 'leo1com.1',
                            'line' => '搜搜',
                            'hash' => '46e3fe384eafcc452abf6def951a9d93bcb9ff04',
                        ],
                    ],
                    'match' => [
                        [
                            'id' => 438037897,
                            'ttl' => 485866,
                            'value' => 'hiero7.leo1.com',
                            'enabled' => true,
                            'name' => 'leo1com.1',
                            'line' => '默认',
                            'hash' => '88d0da415a453849ae2e63588eecec7c69f583d2',
                        ],
                        [
                            'id' => 438038957,
                            'ttl' => 485866,
                            'value' => 'hiero7.leo1.com',
                            'enabled' => true,
                            'name' => 'leo1com.1',
                            'line' => '国外',
                            'hash' => 'b402d449ee70e25e0ba73374d92b61f8b1c5e504',
                        ],
                    ],
                ],
            ]);
    }
}
