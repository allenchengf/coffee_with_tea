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
    public function mappingData()
    {
        $scanneds = $this->regionList->map(function ($region, $key) {
            $scannedObj = new \stdClass();
            $scannedObj->latency = null;
            
            if ($this->nameIsChina($region->country->name)) {
                if ($this->nameIsAll($region->location)) {
                    $scannedObj->latency = $this->nameIsAll($region->isp) ?
                    $this->getAllListAvg() :
                    $this->getISPAvg($region->isp);
                } else if (!$this->nameIsAll($region->location) && !$this->nameIsAll($region->isp)) {
                    $scannedObj->latency = $this->getRegionIsp($region->location, $region->isp);
                }
            }

            $scannedObj->location_networks = $region;

            return $scannedObj;
        });

        return $scanneds;
    }

    private function nameIsAll(string $name): bool
    {
        return strtolower($name) === 'all';
    }

    private function nameIsChina(string $name): bool
    {
        return strtolower($name) == 'china';
    }
}
