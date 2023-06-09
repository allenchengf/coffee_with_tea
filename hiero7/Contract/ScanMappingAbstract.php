<?php

namespace Hiero7\Contract;

use Illuminate\Support\Collection;

abstract class ScanMappingAbstract
{
    public $ispMappingKey = [
        'China Telecom' => 'ct',
        'china telecom' => 'ct',
        'China Unicom' => 'cu',
        'china unicom' => 'cu',
        'China Mobile' => 'cm',
        'china mobile' => 'cm',
        'All' => 'all',
        'all' => 'all',
    ];

    //小數點保留幾位
    protected $pointReservation = 2;

    protected $regionList, $listData, $crawlerData, $allDataList = [];

    abstract public function __construct(array $crawlerData = [], Collection $regionList);

    /**
     * 需要實作的 Function mappingData
     *
     * 輸出 Scan Platform Scan 的 Mapping Data
     *
     * 會根據 $this->regionList Mapping Latency
     *
     * 輸出 example
     *
     * [
     *     [
     *         "latency" => 333.33,
     *         "location_networks" => LocationNetwork Object
     *     ],
     *     [
     *         "latency" => 999.33,
     *         "location_networks" => LocationNetwork Object
     *     ]
     * ]
     *
     * @return collection
     */
    abstract public function mappingData(): collection;

    /**
     * 將爬蟲的資料處理成特定格式， latency 要介於 0 < 這裡才會用 < 1000
     *
     * [ispEn][provinceEn] = latency
     * [chinz]] = latency
     *
     * @param array $crawlerResult 爬蟲的 result Data
     * @return Collection
     */
    protected function filterData(array $crawlerResult = []): Collection
    {
        $mappingList = [];

        collect($crawlerResult)->map(function ($item) use (&$mappingList) {

            if (!$this->checkCrawlerFormat($item)) {
                return false;
            }

            $mappingList[strtolower($item['ispEn'])][strtolower($item['provinceEn'])][] = $item['latency'];
            $this->allDataList[] = $item['latency'];
        });

        $this->listData = $this->calcRegionAvg($mappingList);

        return $this->listData;
    }

    /**
     * get 全部監測點的 latency Average
     *
     * @param array $ispList
     * @return integer|null
     */
    protected function getAllListAvg()
    {
        $allAvg = collect($this->allDataList)->avg();

        return ($allAvg > 0) ? round($allAvg, $this->pointReservation) : null;
    }

    /**
     * get ISP latency Average
     *
     * @param array $ispList
     * @return integer|null
     */
    protected function getISPAvg(string $ispName)
    {
        $shortISPName = $this->getShortISPName($ispName);

        return isset($this->listData[$shortISPName]) ?
        round(collect($this->listData[$shortISPName])->avg(), $this->pointReservation) :
        null;
    }

    /**
     * get Region 的 ISP Latency
     *
     * @param string $regionName
     * @param string $ispName
     * @return integer|null
     */
    protected function getRegionIsp(string $regionName, string $ispName)
    {
        $shortISPName = $this->getShortISPName($ispName);

        if (!isset($this->listData[$shortISPName])) {
            return null;
        }

        return $this->listData[$shortISPName][strtolower($regionName)] ?? null;
    }

    /**
     * get ISP 縮寫
     *
     * @param string $ispName
     * @return string|null
     */
    protected function getShortISPName(string $ispName)
    {
        return $this->ispMappingKey[strtolower($ispName)] ?? null;
    }

    /**
     * 檢查爬蟲的格式，是否符合成為計算資料
     *
     * 檢查 1 : array key 沒有 latency
     * 檢查 2 : latency <= 0
     * 檢查 3 : latency >= 1000
     * 檢查 4 : array key 沒有 ispEn
     * 檢查 5 : array key 沒有 provinceEn
     *
     * false 表示上面條件其中一個符合
     * true  表示格式沒問題，可以使用
     *
     * @param array $item
     * @return boolean
     */
    private function checkCrawlerFormat(array $item = []): bool
    {
        return !(!isset($item['latency']) ||
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
                return round(collect($item)->avg(), $this->pointReservation);
            });
        });
    }
}
