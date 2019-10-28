<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\ScanProviderController;
use App\Http\Requests\ScanProviderRequest as Request;
use Carbon\Carbon;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\ScanLogRepository;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Services\ScanProviderService;
use Illuminate\Support\Facades\Cache;
use Mockery as m;
use Tests\TestCase;

class ScanProviderTest extends TestCase
{
    /**
     * @var ScanProviderController
     */
    private $controller, $domainRepository, $scanLogRepository;
    private $spyLocationDnsSettingService;

    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->seed('LocationDnsSettingSeeder');
        $this->setLocationNetwork();

        app()->call([$this, 'repository']);
        $this->cdnProvider = new CdnProvider();
        $this->setCdnProviderScannable();

        $this->spyLocationDnsSettingService = m::spy(LocationDnsSettingService::class);

        $this->fakeScanProviderService = new FakeScanProviderService($this->spyLocationDnsSettingService, $this->scanLogRepository);

        $this->controller = new ScanProviderController(
            $this->fakeScanProviderService,
            $this->scanLogRepository,
            $this->cdnProviderRepository,
            $this->lineRepository
        );

        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1);

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function repository(ScanLogRepository $scanLogRepository,
        DomainRepository $domainRepository,
        CdnProviderRepository $cdnProviderRepository,
        LineRepository $lineRepository
    ) {
        $this->scanLogRepository = $scanLogRepository;
        $this->domainRepository = $domainRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
        $this->lineRepository = $lineRepository;
    }

    /**
     *
     *
     * @test
     */
    public function checkLockTime()
    {
        $expiresAt = now()->addMinutes(1);

        Cache::put("Scan_Group_Is_Lock_1", $expiresAt, $expiresAt);

        $this->response = $this->controller->checkLockTime();

        $this->checkoutResponse();

        $this->assertEquals(59, $this->responseArrayData['data']['lock_second']);
    }

    /**
     * @test
     *
     */
    public function setScanCoolTime()
    {
        $method = $this->getPrivateMethod(ScanProviderController::class, 'setScanCoolTime');

        $method->invokeArgs($this->controller, []);

        $this->assertEquals(1, Cache::get("Scan_Group_Lock_1"));
        $this->assertNull(Cache::get("Scan_Group_Is_Lock_1"));

        $method->invokeArgs($this->controller, []);

        $this->assertEquals(2, Cache::get("Scan_Group_Lock_1"));
        $this->assertNull(Cache::get("Scan_Group_Is_Lock_1"));

        $method->invokeArgs($this->controller, []);

        $this->assertEquals(3, Cache::get("Scan_Group_Lock_1"));

        $seconds = Cache::get("Scan_Group_Is_Lock_1")->diffInSeconds(Carbon::now());
        $this->assertEquals('integer', gettype($seconds));
    }

    private function shouldUseDecideAction()
    {
        $this->spyLocationDnsSettingService
            ->shouldHaveReceived('decideAction')
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

    private function checkStatusAndReturnData($response, int $statusCode = 200)
    {
        $this->assertEquals(200, $response->status());

        return json_decode($response->getContent(), true);
    }

    private function setLocationNetwork()
    {
        $data = [
            [
                "id" => 1,
                'mapping_value' => 'Zhejiang Wenzho Telecom',
            ],
            [
                "id" => 2,
                'mapping_value' => 'Shaanxi Xi an Telecom',
            ],
            [
                "id" => 3,
                'mapping_value' => 'Jiangsu Suqian Unicom',
            ],

        ];

        foreach ($data as $key => $value) {
            LocationNetwork::find($value['id'])->update($value);
        }
    }

    private function setCrawData()
    {
        $this->fakeScanProviderService->setcrawlerData([
            "url" => "www.hiero7.com",
            "time" => 1565750039,
            "source" => "chinaz",
            "method" => "ping",
            "results" => [
                [
                    "nameEn" => "Zhejiang Wenzho Telecom",
                    "latency" => 555,
                ],
                [
                    "nameEn" => "Shaanxi Xi'an Telecom",
                    "latency" => 221,
                ],
                [
                    "nameEn" => "Jiangsu Suqian Unicom",
                    "latency" => 331,
                ],
                [
                    "nameEn" => "Jiangsu Suqian Telecom",
                    "latency" => 123,
                ],
            ],
        ]);
    }

    private function setCdnProviderScannable()
    {
        $this->cdnProvider->where('scannable', '0')->update([
            'status' => 'active',
            'scannable' => 1,
        ]);
    }

}

class FakeScanProviderService extends ScanProviderService
{

    protected $crawlerData = [];

    public function setcrawlerData(array $data = [])
    {
        return $this->crawlerData = $data;
    }

    protected function curlToCrawler($url, array $data = [])
    {
        return $this->crawlerData;
    }

}
