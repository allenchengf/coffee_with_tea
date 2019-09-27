<?php

namespace Tests\Unit\Services;

use Hiero7\Contract\ScanMappingAbstract;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ScanMappingTest extends TestCase
{
    use DatabaseMigrations;
    protected $domainService, $domain, $jwtPayload = [];

    protected function setUp()
    {
        parent::setUp();
        $this->abstract = new FakeAbstract([], collect([]));
    }

    /**
     * @test
     */
    public function checkCrawlerFormat()
    {
        $method = $this->getPrivateMethod(FakeAbstract::class, 'checkCrawlerFormat');

        $checkIsTrue = [
            [
                'latency' => 999.999,
                'ispEn' => 'CT',
                'provinceEn' => 'Shaanxi',
            ], [
                'latency' => 1.1,
                'ispEn' => 'CT',
                'provinceEn' => 'Zhejiang',
            ],
        ];

        foreach ($checkIsTrue as $check) {
            $result = $method->invokeArgs($this->abstract, [$check]);
            $this->assertTrue($result);
        }

        $checkIsFalse = [
            [
                'latency' => 1000,
                'ispEn' => 'CT',
                'provinceEn' => null,
            ], [
                'latency' => 1000,
                'ispEn' => ' ',
                'provinceEn' => ' ',
            ], [
                'latency' => 1000,
                'ispEn' => 'CT',
                'provinceEn' => 'Shaanxi',
            ], [
                'latency' => 0,
                'ispEn' => 'CT',
                'provinceEn' => 'Zhejiang',
            ], [
                'latency' => 222,
                'ispEn' => 'CT',
            ], [
                'latency' => 333,
                'provinceEn' => 'Zhejiang',
            ], [
                'latency' => 111,
                'provinceEn' => 'Zhejiang',
            ], [
                'latency' => 333,
            ], [
                'ispEn' => 'CT',
            ], [
                'provinceEn' => 'Zhejiang',
            ], [

            ],
        ];

        foreach ($checkIsFalse as $check) {
            $result = $method->invokeArgs($this->abstract, [$check]);
            $this->assertFalse($result);
        }
    }

}

class FakeAbstract extends ScanMappingAbstract
{
    public function __construct(array $crawlerData = [], Collection $regionList)
    {
        return $this;
    }

    public function mappingData(): collection
    {
        return $this;
    }
}
