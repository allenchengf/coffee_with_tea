<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\ScanProviderController;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\CdnService;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Services\ScanProviderService;
use Mockery as m;
use Tests\TestCase;

class ScanProviderTest extends TestCase
{
    /**
     * @var ScanProviderController
     */
    private $controller;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');
        
        app()->call([$this, 'repository']);

        $this->mockcdnService = m::mock(CdnService::class);
        $this->mockdnsPodRecordSyncService = m::mock(DnsPodRecordSyncService::class);

        $this->controller = new ScanProviderController(
            (
                new ScanProviderService(
                    $this->mockcdnService,
                    $this->mockdnsPodRecordSyncService,
                    $this->locationDnsSettingRepository)
            )
        );

    }
    public function repository(LocationDnsSettingRepository $locationDnsSettingRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function scanInedex()
    {
        $this->assertTrue(true);
        $response = $this->controller->index();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                '17ce',
                'chinaz',
            ]
            , $data['data']
        );
    }
}
