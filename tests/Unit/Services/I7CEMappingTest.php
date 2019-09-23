<?php

namespace Tests\Unit\Services;

use Hiero7\Repositories\LineRepository;
use Hiero7\Services\I7CEMappingService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class I7CEMappingTest extends TestCase
{
    use DatabaseMigrations;

    private $locationDnsSettingService, $lineRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->seed();
        $this->seed('ScanPlatformTableSeeder');
        app()->call([$this, 'repository']);

        $this->service = new I7CEMappingService($this->setCrawlerData(), $this->lineRepository->getRegion());

        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);
    }

    public function repository(LineRepository $lineRepository)
    {
        $this->lineRepository = $lineRepository;
    }

    /**
     * @test
     */
    public function mappingData()
    {
        $result = $this->service->mappingData();

        $this->assertEquals(373, (int) $result[0]->latency);
        $this->assertEquals(null, (int) $result[1]->latency);
        $this->assertEquals(345, (int) $result[2]->latency);
    }

    private function setCrawlerData()
    {
        $data = json_decode('{
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
                    "latency": 300
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "CU",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "Henan",
                    "nameEn": "",
                    "nameCn": "河南郑州联通",
                    "latency": 555
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "CU",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "Shanxi",
                    "nameEn": "",
                    "nameCn": "山西运城联通",
                    "latency": 332
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
                    "latency": 344
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
                    "latency": 231
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
                    "latency": 333
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
                    "latency": 422
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
                    "latency": 214
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
                    "latency": 778
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "CT",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "Jiangxi",
                    "nameEn": "",
                    "nameCn": "江西吉安电信",
                    "latency": 200
                },
                {
                    "source": "chinaz",
                    "area": "NOT IN \"Crawler Column Format Ref v1.2.xlsx\" at 2018/12/10",
                    "ispEn": "CT",
                    "ispCn": "",
                    "countryEn": "CN",
                    "countryCn": "中国",
                    "provinceCn": "",
                    "provinceEn": "Jiangxi",
                    "nameEn": "",
                    "nameCn": "江西吉安电信",
                    "latency": 400
                }]
        }', true);
        return $data;
    }
}
