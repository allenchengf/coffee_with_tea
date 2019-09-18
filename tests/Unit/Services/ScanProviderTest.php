<?php

namespace Tests\Unit\Services;

use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\DomainGroupMapping;use Hiero7\Models\ScanLog;
use Hiero7\Models\ScanPlatform;
use Hiero7\Repositories\DomainRepository;
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
        $this->seed('ScanPlatformTableSeeder');
        $this->seed('CdnProviderSeeder');
        $this->seed('LocationNetworkTableSeeder');

        app()->call([$this, 'repository']);

        $this->mockLocationDnsSettingService = m::mock(LocationDnsSettingService::class);
        $this->mockScanLogRepository = m::mock(ScanLogRepository::class);
        $this->service = new ScanProviderService($this->mockLocationDnsSettingService, $this->mockScanLogRepository);
        $this->domain = Domain::find(1);
        $this->scanPlatform = ScanPlatform::find(1);
        $this->cdnProvider = CdnProvider::find(1);
        $this->cdnProvider->scannable = 1; // 設定可被爬
        $this->setScanLogs();

        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);
    }

    public function repository(ScanLogRepository $scanLogRepository, DomainRepository $domainRepository)
    {
        $this->scanLogRepository = $scanLogRepository;
        $this->domainRepository = $domainRepository;
    }

    /**
     * @test
     */
    public function changeDomainRegionByScanData()
    {
        $this->service = new ScanProviderService($this->mockLocationDnsSettingService, $this->scanLogRepository);

        $this->setDecideAction(['differentGroup', true, 'DNS Pod Error', 'differentGroup', false]);

        $result = $this->service->changeDomainRegionByScanData($this->domain);

        $this->assertEquals($result[0]['status'], true);

        $this->assertEquals($result[1]['status'], 'DNS Pod Error');

        $this->assertEquals($result[2]['status'], false);
    }

    /**
     * @test
     */
    public function changeDomainGroupRegionByScanData()
    {
        $this->service = new ScanProviderService($this->mockLocationDnsSettingService, $this->scanLogRepository);

        $this->setDecideAction([
            'differentGroup', true, 'DNS Pod Error', 'differentGroup', false,
            'differentGroup', 'DNS Pod Error', true, 'DNS Pod Error 123',
        ]);

        $this->setDomainGroup();

        $domainGroup = DomainGroup::find(1);

        $result = $this->service->changeDomainGroupRegionByScanData($domainGroup);

        $this->assertEquals($result[0]['result'][0]['status'], true);
        $this->assertEquals($result[0]['result'][1]['status'], 'DNS Pod Error');
        $this->assertEquals($result[0]['result'][2]['status'], false);

        $this->assertEquals($result[1]['result'][0]['status'], 'DNS Pod Error');
        $this->assertEquals($result[1]['result'][1]['status'], true);
        $this->assertEquals($result[1]['result'][2]['status'], 'DNS Pod Error 123');
    }

    /**
     * @test
     */
    public function indexScannedData()
    {
        $this->setIndexLatestLogs();

        $result = $this->service->indexScannedData($this->scanPlatform, $this->cdnProvider);

        $this->assertEquals($result[0]->latency, 1000);
        $this->assertEquals($result[0]->location_networks->id, 3);
    }

    /**
     * @test
     */
    public function mappingData()
    {
        $crawlerData = $this->setCrawlerData();

        $result = $this->service->mappingData($crawlerData);

        $this->assertEquals($result[0]->latency, 1000);
        $this->assertEquals($result[0]->location_networks->id, 1);
    }

    /**
     * @test
     * @return void
     */
    public function changeAllRegionByScanData()
    {
        $this->service = new ScanProviderService($this->mockLocationDnsSettingService, $this->scanLogRepository);

        $this->setDecideAction();

        $result = $this->service->changeAllRegionByScanData($this->domainRepository->getDomainByUserGroup());

        $expectedCount = $this->domainRepository->getDomainByUserGroup()->count();

        $this->assertCount($expectedCount, $result);
    }

    private function setDecideAction(array $actionList = [])
    {
        $actionList = count($actionList) > 0 ? $actionList : [true];

        $this->mockLocationDnsSettingService
            ->shouldReceive('decideAction')
            ->andReturn(...$actionList);
    }

    private function setDomainGroup()
    {

        $domainGroup = [
            'id' => 1,
            'user_group_id' => 1,
            'name' => str_random(6),
        ];

        DomainGroup::insert($domainGroup);

        $domainGroupMappings = [
            [
                'domain_id' => 1,
                'domain_group_id' => 1,
            ], [
                'domain_id' => 4,
                'domain_group_id' => 1,
            ],
        ];

        foreach ($domainGroupMappings as $domainGroupMapping) {
            DomainGroupMapping::insert($domainGroupMapping);
        }

    }

    private function setIndexLatestLogs()
    {
        $this->mockScanLogRepository
            ->shouldReceive('indexLatestLogs')
            ->andReturn((object) ["latency" => "1000,169,1000", "location_network_id" => "3,2,1", "created_at" => \Carbon\Carbon::now()]);
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

    private function setCrawlerData()
    {
        $data = json_decode('{
            "url": "www.hiero7.com",
            "time": 1565750039,
            "source": "chinaz",
            "method": "ping",
            "isps": {
                "ct": 179.2,
                "cu": 188.6,
                "cm": null,
                "domestic": null,
                "overseas": null
            },
            "results": [
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Wenzho Telecom",
                    "nameCn": "浙江温州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "河南郑州联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "陕西",
                    "provinceEn": "Shaanxi",
                    "nameEn": "Shaanxi Xi\'an Telecom",
                    "nameCn": "陕西西安电信",
                    "latency": 188,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "山西运城联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Unicom",
                    "nameCn": "江苏宿迁联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Telecom",
                    "nameCn": "江苏宿迁电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "四川",
                    "provinceEn": "Sichuan",
                    "nameEn": "Sichuan Chengdu Telecom",
                    "nameCn": "四川成都电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Foshan Telecom",
                    "nameCn": "广东佛山电信",
                    "latency": 1000,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖南",
                    "provinceEn": "Hunan",
                    "nameEn": "Hunan Changsha Telecom",
                    "nameCn": "湖南长沙电信",
                    "latency": 214,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Telecom",
                    "nameCn": "江苏宿迁电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "江西吉安电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Jiaxing Unicom",
                    "nameCn": "浙江嘉兴联通",
                    "latency": 157,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Unicom",
                    "nameCn": "江苏徐州联通",
                    "latency": 174,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖北",
                    "provinceEn": "Hubei",
                    "nameEn": "Hubei Wuhan Telecom",
                    "nameCn": "湖北武汉电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "新疆",
                    "provinceEn": "Xinjiang",
                    "nameEn": "Xinjiang Hami Telecom",
                    "nameCn": "新疆哈密电信",
                    "latency": 266,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "山东",
                    "provinceEn": "Shandong",
                    "nameEn": "Shandong Zaozhuang Unicom",
                    "nameCn": "山东枣庄联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "黑龙江哈尔滨联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖北",
                    "provinceEn": "Hubei",
                    "nameEn": "Hubei Xiantao Telecom",
                    "nameCn": "湖北仙桃电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "湖北宜昌电信",
                    "latency": 182,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "山东",
                    "provinceEn": "Shandong",
                    "nameEn": "Shandong Jinan Unicom",
                    "nameCn": "山东济南联通",
                    "latency": 266,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "安徽",
                    "provinceEn": "Anhui",
                    "nameEn": "Anhui Hefei Telecom",
                    "nameCn": "安徽合肥电信",
                    "latency": 144,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Shenzhen Telecom",
                    "nameCn": "广东深圳电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "陕西",
                    "provinceEn": "Shaanxi",
                    "nameEn": "Shaanxi Xi\'an Telecom",
                    "nameCn": "陕西西安电信",
                    "latency": 175,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Shaoxing Telecom",
                    "nameCn": "浙江绍兴电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Shenzhen Telecom",
                    "nameCn": "广东深圳电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Unicom",
                    "nameCn": "江苏徐州联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "重庆电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Yangzhou Telecom",
                    "nameCn": "江苏扬州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Zhenjiang Telecom",
                    "nameCn": "江苏镇江电信",
                    "latency": 141,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖南",
                    "provinceEn": "Hunan",
                    "nameEn": "Hunan Hengyang Telecom",
                    "nameCn": "湖南衡阳电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "山东",
                    "provinceEn": "Shandong",
                    "nameEn": "Shandong Zaozhuang Telecom",
                    "nameCn": "山东枣庄电信",
                    "latency": 165,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "湖北宜昌电信",
                    "latency": 182,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Unicom",
                    "nameCn": "江苏徐州联通",
                    "latency": 156,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江西",
                    "provinceEn": "Jiangxi",
                    "nameEn": "Jiangxi Xinyu Telecom",
                    "nameCn": "江西新余电信",
                    "latency": 196,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Guangzhou Telecom",
                    "nameCn": "广东广州电信",
                    "latency": 165,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Shenzhen Unicom",
                    "nameCn": "广东深圳联通",
                    "latency": 186,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Foshan Telecom",
                    "nameCn": "广东佛山电信",
                    "latency": 186,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Telecom",
                    "nameCn": "江苏徐州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Shaoxing Telecom",
                    "nameCn": "浙江绍兴电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "河南郑州联通",
                    "latency": 198,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖南",
                    "provinceEn": "Hunan",
                    "nameEn": "Hunan Hengyang Telecom",
                    "nameCn": "湖南衡阳电信",
                    "latency": 178,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Changzhou Telecom",
                    "nameCn": "江苏常州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Telecom",
                    "nameCn": "江苏宿迁电信",
                    "latency": 1000,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Telecom",
                    "nameCn": "江苏宿迁电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Zhenjiang Telecom",
                    "nameCn": "江苏镇江电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "湖南长沙联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Unicom",
                    "nameCn": "江苏徐州联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Taizhou Telecom",
                    "nameCn": "江苏泰州电信",
                    "latency": 146,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Unicom",
                    "nameCn": "江苏宿迁联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "陕西咸阳电信",
                    "latency": 183,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华北",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "山西",
                    "provinceEn": "Shanxi",
                    "nameEn": "Shanxi Taiyuan Unicom",
                    "nameCn": "山西太原联通",
                    "latency": 182,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Unicom",
                    "nameCn": "江苏宿迁联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Suqian Unicom",
                    "nameCn": "江苏宿迁联通",
                    "latency": 1000,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Taizhou Telecom",
                    "nameCn": "江苏泰州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Jinhua Unicom",
                    "nameCn": "浙江金华联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "四川",
                    "provinceEn": "Sichuan",
                    "nameEn": "Sichuan Chengdu Telecom",
                    "nameCn": "四川成都电信",
                    "latency": 191,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "东北",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "辽宁",
                    "provinceEn": "Liaoning",
                    "nameEn": "Liaoning Dalian Unicom",
                    "nameCn": "辽宁大连联通",
                    "latency": 178,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Zhenjiang Telecom",
                    "nameCn": "江苏镇江电信",
                    "latency": 148,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "浙江",
                    "provinceEn": "Zhejiang",
                    "nameEn": "Zhejiang Ningbo Telecom",
                    "nameCn": "浙江宁波电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "四川",
                    "provinceEn": "Sichuan",
                    "nameEn": "Sichuan Mianyang Telecom",
                    "nameCn": "四川绵阳电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Taizhou Telecom",
                    "nameCn": "江苏泰州电信",
                    "latency": 146,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CM",
                    "ispCn": "移动",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Shenzhen Mobile",
                    "nameCn": "广东深圳移动",
                    "latency": 1000,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "东北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "辽宁",
                    "provinceEn": "Liaoning",
                    "nameEn": "Liaoning Dalian Telecom",
                    "nameCn": "辽宁大连电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Zhenjiang Telecom",
                    "nameCn": "江苏镇江电信",
                    "latency": 185,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "山东",
                    "provinceEn": "Shandong",
                    "nameEn": "Shandong Zaozhuang Unicom",
                    "nameCn": "山东枣庄联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "湖北宜昌电信",
                    "latency": 192,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "江西九江电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Yangzhou Telecom",
                    "nameCn": "江苏扬州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Zhenjiang Telecom",
                    "nameCn": "江苏镇江电信",
                    "latency": 157,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "东北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "辽宁",
                    "provinceEn": "Liaoning",
                    "nameEn": "Liaoning Dalian Telecom",
                    "nameCn": "辽宁大连电信",
                    "latency": 255,
                    "packetloss": 0
                },
                {
                    "source": "chinaz",
                    "area": "西南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "四川",
                    "provinceEn": "Sichuan",
                    "nameEn": "Sichuan Mianyang Telecom",
                    "nameCn": "四川绵阳电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "河南新乡电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CM",
                    "ispCn": "移动",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "广东",
                    "provinceEn": "Guangdong",
                    "nameEn": "Guangdong Shenzhen Mobile",
                    "nameCn": "广东深圳移动",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "中南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "湖北",
                    "provinceEn": "Hubei",
                    "nameEn": "Hubei Wuhan Telecom",
                    "nameCn": "湖北武汉电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "东北",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "辽宁",
                    "provinceEn": "Liaoning",
                    "nameEn": "Liaoning Anshan Telecom",
                    "nameCn": "辽宁鞍山电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "西南",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "云南",
                    "provinceEn": "Yunnan",
                    "nameEn": "Yunnan Kunming Telecom",
                    "nameCn": "云南昆明电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CU",
                    "ispCn": "联通",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "福建",
                    "provinceEn": "Fujian",
                    "nameEn": "Fujian Fuzhou Unicom",
                    "nameCn": "福建福州联通",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "华东",
                    "ispEn": "CT",
                    "ispCn": "电信",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "江苏",
                    "provinceEn": "Jiangsu",
                    "nameEn": "Jiangsu Xuzhou Telecom",
                    "nameCn": "江苏徐州电信",
                    "latency": 1000,
                    "packetloss": 100
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "",
                    "nameEn": "",
                    "nameCn": "河南新乡电信",
                    "latency": 1000,
                    "packetloss": 100
                }
            ],
            "mismatches": [
                "河南郑州联通",
                "山西运城联通",
                "江西吉安电信",
                "黑龙江哈尔滨联通",
                "湖北宜昌电信",
                "重庆电信",
                "湖北宜昌电信",
                "河南郑州联通",
                "湖南长沙联通",
                "陕西咸阳电信",
                "湖北宜昌电信",
                "江西九江电信",
                "河南新乡电信",
                "河南新乡电信"
            ]
        }', true);
        return (object) $data;
    }
}
