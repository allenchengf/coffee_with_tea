<?php
namespace Hiero7\Services;

use Hiero7\Contract\ScanMappingAbstract;
use Illuminate\Support\Collection;

class I7CEMappingService extends ScanMappingAbstract
{
    public $crawlerData, $listData;

    protected $regionList;

    public function __construct(array $crawlerData = [], Collection $regionList)
    {
        $this->crawlerData = $crawlerData;

        $this->regionList = $regionList;

        $this->filterData($this->crawlerData['results'] ?? []);
    }

    /**
     * @param $crawlerData
     * @return Collection
     */
    public function mappingData(): collection
    {
        return $this->regionList->map(function ($region, $key) {
            $scannedObj = new \stdClass();
            $scannedObj->latency = null;
            $scannedObj->location_networks = $region;

            // 第一層判斷，是否為 China 內的 Region
            if ($this->nameIsChina($region->country->name)) {

                // 第二層判斷，Location 是否為 All
                if ($this->nameIsAll($region->location)) {

                    // 第三層判斷，如果 ISP 也是 All，表示要取得 China All 的 latency
                    // 不是，表示取得 ISP ，在 China latency
                    $scannedObj->latency = $this->nameIsAll($region->isp) ?
                    $this->getAllListAvg() :
                    $this->getISPAvg($region->isp);
                } else if (!$this->nameIsAll($region->location) && !$this->nameIsAll($region->isp)) {

                    // 如果 Location 不是 All ，表示是指特定地區的 ISP 的 latency
                    $scannedObj->latency = $this->getRegionIsp($region->location, $region->isp);
                }
            }

            return $scannedObj;
        });
    }

    /**
     * 判斷輸入的名稱是否為 All | all
     *
     * @param string $name
     * @return boolean
     */
    private function nameIsAll(string $name): bool
    {
        return strtolower($name) === 'all';
    }

    /**
     * 判斷輸入的名稱是否為 china | china
     *
     * @param string $name
     * @return boolean
     */
    private function nameIsChina(string $name): bool
    {
        return strtolower($name) == 'china';
    }
}
