<?php

namespace Tests\Unit\Services;

use Hiero7\Models\Domain;
use Hiero7\Models\ScanLog;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Services\ScanProviderService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Mockery as m;

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

        $this->mockLocationDnsSettingService = m::mock(LocationDnsSettingService::class);

        $this->service = new ScanProviderService($this->mockLocationDnsSettingService);
        $this->domain = Domain::find(1);
        $this->setScanLogs();
    }

    public function service()
    {
    }


    /**
     * @test
     */
    public function changeDomainRegionByScanData()
    {
        $this->setDecideAction(['differentGroup', true, 'DNS Pod Error', 'differentGroup', true]);

        $result = $this->service->changeDomainRegionByScanData($this->domain);

        $this->assertEquals($result[0]['status'], true);
        $this->assertEquals($result[1]['status'], 'DNS Pod Error');
        $this->assertEquals($result[2]['status'], true);
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
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 2,
                "cdn_provider_id" => 1,
                "latency" => "100",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 3,
                "cdn_provider_id" => 1,
                "latency" => "100",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 2,
                "latency" => "200",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 2,
                "cdn_provider_id" => 2,
                "latency" => "200",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 3,
                "cdn_provider_id" => 2,
                "latency" => "200",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
            ],
            [
                "scan_platform_id" => 1,
                "location_network_id" => 1,
                "cdn_provider_id" => 3,
                "latency" => "300",
            ],
        ];

        foreach ($data as $key => $value) {
            ScanLog::insert($value);
        }
    }
}
