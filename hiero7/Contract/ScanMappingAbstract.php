<?php

namespace Hiero7\Contract;

use Illuminate\Support\Collection;

abstract class ScanMappingAbstract
{
    public $isp = [
        'China Telecom' => 'ct',
        'China Unicom' => 'cu',
        'China Mobile' => 'cm',
    ];

    /**
     * 將爬蟲的資料處理成特定格式
     *
     * [ispEn][provinceEn] = latency
     * [chinz]] = latency
     *
     * @param array $list
     * @return Collection
     */
    protected function filterData(array $list = []): Collection
    {
        $mappingList = $china = [];

        collect($list)->map(function ($item) use (&$mappingList, &$china) {

            if ($this->checkCrawlerFormat($item)) {
                return false;
            }

            $mappingList[strtolower($item['ispEn'])][strtolower($item['provinceEn'])][] = $item['latency'];
            $china[] = $item['latency'];
        });

        $mappingList = $this->calcRegionAvg($mappingList);

        if ($mappingList) {
            $mappingList['china'] = round(collect($china)->avg(), 2);
        }

        return $mappingList;
    }
    
    /**
     * 計算 ISP latency Average
     *
     * @param array $ispList
     * @return integer
     */
    protected function calcISPAvg($ispList = []): int
    {
        return collect($ispList)->avg();
    }

    /**
     * 檢查爬蟲的格式，是否符合成為計算資料
     *
     * @param array $item
     * @return boolean
     */
    private function checkCrawlerFormat(array $item = []) :bool
    {
        return (!isset($item['latency']) ||
            $item['latency'] <= 0 ||
            $item['latency'] >= 1000 ||
            empty($item['ispEn']) ||
            empty($item['provinceEn']));
    }

    /**
     * 計算各個 Region Average
     *
     * @param array $mappingList
     * @return Collection
     */
    private function calcRegionAvg(array $mappingList = []): Collection
    {
        return collect($mappingList)->map(function ($isps) {
            return collect($isps)->map(function ($item) {
                return collect($item)->avg();
            });
        });
    }
}
