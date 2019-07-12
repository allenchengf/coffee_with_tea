<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\v1\DnsPodRecordSyncController;
use App\Http\Requests\DnsPodRecordSyncRequest as Request;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\LocationDnsSetting;
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

        $this->controller = new DnsPodRecordSyncController($this->mockDnsProviderService);
        
        $this->setDomainData();
        $this->setCdnProviderData();
        $this->setCdnData();
        $this->setLocationDnsSettingData();
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

        $this->mockGetRecords();

        $this->mockCheckAPIOutput();

        $response = $this->controller->checkDataDiff($request, $this->domain);

        $data = $this->checkStatusAndReturnData($response);
    }

    private function mockGetRecords(array $data = [])
    {
        $record = [
            "records" => [
                [
                    "id" => "437764331",
                    "ttl" => "601",
                    "value" => "hiero7.leo1.com.",
                    "enabled" => "1",
                    "status" => "enabled",
                    "updated_on" => "2019-07-11 14:57:02",
                    "name" => "leo1com.1",
                    "line" => "默认",
                    "line_id" => "0",
                    "type" => "CNAME",
                    "weight" => null,
                    "monitor_status" => "",
                    "remark" => "",
                    "use_aqb" => "no",
                    "mx" => "0",
                ], [
                    "id" => "437764337",
                    "ttl" => "701",
                    "value" => "cloudflare.leo1.com.",
                    "enabled" => "1",
                    "status" => "enabled",
                    "updated_on" => "2019-07-11 14:57:04",
                    "name" => "leo1com.1",
                    "line" => "联通",
                    "line_id" => "10=1",
                    "type" => "CNAME",
                    "weight" => null,
                    "monitor_status" => "",
                    "remark" => "",
                    "use_aqb" => "no",
                    "mx" => "0",
                ], [
                    "id" => "437764333",
                    "ttl" => "601",
                    "value" => "hiero7.leo1.com.",
                    "enabled" => "1",
                    "status" => "enabled",
                    "updated_on" => "2019-07-11 14:57:03",
                    "name" => "leo1com.1",
                    "line" => "国外",
                    "line_id" => "3=0",
                    "type" => "CNAME",
                    "weight" => null,
                    "monitor_status" => "",
                    "remark" => "",
                    "use_aqb" => "no",
                    "mx" => "0",
                ], [
                    "id" => "437764335",
                    "ttl" => "701",
                    "value" => "cloudflare.leo1.com.",
                    "enabled" => "1",
                    "status" => "enabled",
                    "updated_on" => "2019-07-11 14:57:03",
                    "name" => "leo1com.1",
                    "line" => "国内",
                    "line_id" => "7=0",
                    "type" => "CNAME",
                    "weight" => null,
                    "monitor_status" => "",
                    "remark" => "",
                    "use_aqb" => "no",
                    "mx" => "0",
                ],
            ],
        ];

        $data = [
            'message' => 'test Message',
            'errorCode' => null,
            'data' => $record,
        ];

        $this->mockDnsProviderService
            ->shouldReceive('getRecords')
            ->andReturn($data);
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
                "ttl" => 601,
            ], [
                "id" => 2,
                "ttl" => 701,
            ], [
                "id" => 3,
                "ttl" => 801,
            ]
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
}
