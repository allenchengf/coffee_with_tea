<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\CdnProviderController;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\v1\ScanProviderController;

class ScanPlatormTest extends TestCase
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
        $this->controller = new ScanProviderController();

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
                'chinaz'
            ]
            ,$data['data']
        );
    }
}
