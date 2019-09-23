<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Services\ChinaZMappingService;
use Hiero7\Repositories\LineRepository;

use function GuzzleHttp\json_decode;

class ChinaZMappingServiceTest extends TestCase
{
    

    protected function setUp()
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed();
        
        app()->call([$this, 'getRegion']);

        $this->crawlerData = [
            "url" => "http://www.hiero7.com",
            "time" => 1565750039,
            "source"=> "chinaz",
            "method"=> "ping",
            "isps"=> [
                "ct"=> 179.2,
                "cu"=> 188.6,
                "cm"=> null,
                "domestic"=> null,
                "overseas"=> null
            ],
            "results"=> [
                [
                    "source"=> "chinaz",
                    "area"=> "华东",
                    "ispEn"=> "CT",
                    "ispCn"=> "电信",
                    "countryEn"=> "CN",
                    "countryCn"=> "中国",
                    "provinceCn"=> "浙江",
                    "provinceEn"=> "Zhejiang",
                    "nameEn"=> "Zhejiang Wenzho Telecom",
                    "nameCn"=> "浙江温州电信",
                    "latency"=> 400,
                    "packetloss"=> 100
                ],
                [
                    "source"=> "chinaz",
                    "area"=> "中南",
                    "ispEn"=> "CT",
                    "ispCn"=> "电信",
                    "countryEn"=> "CN",
                    "countryCn"=> "中国",
                    "provinceCn"=> "湖北",
                    "provinceEn"=> "Hubei",
                    "nameEn"=> "Hubei Xiantao Telecom",
                    "nameCn"=> "湖北仙桃电信",
                    "latency"=> 600,
                    "packetloss"=> 100
                ],
                [
                    "source"=> "chinaz",
                    "area"=> "西北",
                    "ispEn"=> "CT",
                    "ispCn"=> "电信",
                    "countryEn"=> "CN",
                    "countryCn"=> "中国",
                    "provinceCn"=> "陕西",
                    "provinceEn"=> "Shaanxi",
                    "nameEn"=> "Shaanxi Xi'an Telecom",
                    "nameCn"=> "陕西西安电信",
                    "latency"=> 188,
                    "packetloss"=> 0
                ],[
                    "source"=> "chinaz",
                    "area"=> "西北",
                    "ispEn"=> "CT",
                    "ispCn"=> "电信",
                    "countryEn"=> "CN",
                    "countryCn"=> "中国",
                    "provinceCn"=> "陕西",
                    "provinceEn"=> "Shaanxi",
                    "nameEn"=> "Shaanxi Xi'an Telecom",
                    "nameCn"=> "陕西西安电信",
                    "latency"=> 188,
                    "packetloss"=> 0
                ],[
                    "source"=> "chinaz",
                    "area"=> "华东",
                    "ispEn"=> "CU",
                    "ispCn"=> "联通",
                    "countryEn"=> "CN",
                    "countryCn"=> "中国",
                    "provinceCn"=> "山东",
                    "provinceEn"=> "Shandong",
                    "nameEn"=> "Shandong Jinan Unicom",
                    "nameCn"=> "山东济南联通",
                    "latency"=> 266,
                    "packetloss"=> 0
                ]
            ]
        ];

        $this->service = new ChinaZMappingService($this->crawlerData,$this->locationNetwork);

    }
    
    public function getRegion(LineRepository $lineRepository)
    {
        $this->locationNetwork = $lineRepository->getRegion();
    }
    public function testMapping()
    {
        $result = $this->service->mappingData();

            //  檢查 China 的所有 isp 和 location 
            $this->assertEquals(328.4, $result[0]->latency);
            $this->assertEquals(1, $result[0]->location_networks->country_id);

            // 檢查 非 China 的
            $this->assertEquals(null, $result[1]->latency);
            $this->assertLessThan($result[1]->location_networks->country_id, 1);

            // 檢查 ct 
            $this->assertEquals(396.0, $result[2]->latency);
            $this->assertEquals('ct', $result[2]->location_networks->isp);

            // 檢查 cu 
            $this->assertEquals(266.0, $result[3]->latency);
            $this->assertEquals('cu', $result[3]->location_networks->isp);
    }
}
