<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\ScanProviderController;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\CdnService;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Services\ScanProviderService;
use Mockery as m;
use Tests\TestCase;
use App\Http\Requests\ScanProviderRequest as Request;
use Hiero7\Models\LocationNetwork;

class ScanProviderTest extends TestCase
{
    /**
     * @var ScanProviderController
     */
    private $controller;
    private $spyLocationDnsSettingService;


    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');

        $this->spyLocationDnsSettingService = m::spy(LocationDnsSettingService::class);

        $this->controller = new ScanProviderController(
            (new ScanProviderService($this->spyLocationDnsSettingService))
        );

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

    /**
     * @test
     */
    public function selectAchangeToBCdnProvider()
    {
        $selectCdnProvider = [
            'old_cdn_provider_id' => 1,
            'new_cdn_provider_id' => 2
        ];

        $request = $this->createRequestAndJwt($selectCdnProvider);

        $locationNetwork = LocationNetwork::find(1);

        $response = $this->controller->selectAchangeToBCdnProvider($request, $locationNetwork);

        $this->assertEquals(200, $response->status());

        $this->shouldUseChangeLocationDNSSettion();
    }

    private function shouldUseChangeLocationDNSSettion()
    {
        $this->spyLocationDnsSettingService
            ->shouldHaveReceived('updateSetting')
            ->twice();
    }

    private function createRequestAndJwt(array $requestList = [], int $loginUid = 1, int $LoginUserGroupId = 1)
    {
        $request = new Request();

        $request->merge($requestList);

        $this->addUuidforPayload()
            ->addUserGroupId($LoginUserGroupId)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        return $request;
    }

}
