<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\ScanProviderController;
use App\Http\Requests\ScanProviderRequest as Request;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Repositories\ScanLogRepository;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Services\ScanProviderService;
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

        $this->spyLocationDnsSettingService = m::spy(LocationDnsSettingService::class);
        $this->fakeScanProviderService = new FakeScanProviderService($this->spyLocationDnsSettingService, $this->scanLogRepository);
        $this->controller = new ScanProviderController($this->fakeScanProviderService);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function repository(ScanLogRepository $scanLogRepository, DomainRepository $domainRepository)
    {
        $this->scanLogRepository = $scanLogRepository;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @test
     */
    public function selectAchangeToBCdnProvider()
    {
        $selectCdnProvider = [
            'old_cdn_provider_id' => 1,
            'new_cdn_provider_id' => 2,
        ];

        $request = $this->createRequestAndJwt($selectCdnProvider);

        $locationNetwork = LocationNetwork::find(1);

        $response = $this->controller->changeCDNProviderByIRoute($request, $locationNetwork);

        $this->assertEquals(200, $response->status());

        $this->shouldUseDecideAction();
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
