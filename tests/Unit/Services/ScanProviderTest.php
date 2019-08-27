<?php

namespace Tests\Unit\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\ScanLog;
use Hiero7\Repositories\ScanLogRepository;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Services\ScanProviderService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery as m;
use Tests\TestCase;

class ScanProviderTest extends TestCase
{
    use DatabaseMigrations;

    private $locationDnsSettingService;

    protected function setUp()
    {
        parent::setUp();
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');

        app()->call([$this, 'repository']);

        $this->mockLocationDnsSettingService = m::mock(LocationDnsSettingService::class);

        $this->service = new ScanProviderService($this->mockLocationDnsSettingService, $this->scanLogRepository);

        $this->domain = Domain::find(1);

        $this->setScanLogs();

        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);
    }

    public function repository(ScanLogRepository $scanLogRepository)
    {
        $this->scanLogRepository = $scanLogRepository;
    }

    /**
     * @test
     */
    public function changeDomainRegionByScanData()
    {
        $this->setDecideAction(['differentGroup', true, 'DNS Pod Error', 'differentGroup', false]);

        $result = $this->service->changeDomainRegionByScanData($this->domain);

        $this->assertEquals($result[0]['status'], true);

        $this->assertEquals($result[1]['status'], 'DNS Pod Error');

        $this->assertEquals($result[2]['status'], false);
    }

    private function setDecideAction(array $actionList = [])
    {
        $actionList = count($actionList) > 0 ? $actionList : [true];

        $this->mockLocationDnsSettingService
            ->shouldReceive('decideAction')
            ->andReturn(...$actionList);
    }

    private function setScanLogs()
    {
        $data = [
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 1,
                "latency" => "400",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 2,
                "cdn_provider_id" => 1,
                "latency" => "100",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 3,
                "cdn_provider_id" => 1,
                "latency" => "100",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 2,
                "latency" => "200",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 2,
                "cdn_provider_id" => 2,
                "latency" => "200",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 3,
                "cdn_provider_id" => 2,
                "latency" => "200",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
                "created_at" => "2019-08-23 16:44:55",
                "updated_at" => "2019-08-23 16:44:55",
            ],
        ];

        foreach ($data as $key => $value) {
            ScanLog::insert($value);
        }
    }
}
