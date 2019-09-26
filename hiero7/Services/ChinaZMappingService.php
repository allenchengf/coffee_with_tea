<?php 
namespace Hiero7\Services;

use Hiero7\Contract\ScanMappingAbstract;
use Illuminate\Support\Collection;

class ChinaZMappingService extends ScanMappingAbstract
{
    public function __construct(array $crawlerData = [], Collection $regionList)
    {
        $this->crawlerData = $crawlerData;
        $this->regionList = $regionList;
        $this->filterData($this->crawlerData['results'] ?? []);
    }
    
    public function mappingData(): collection
    { 
        $crawlerISPAvg = collect($this->crawlerData['isps'] ?? []);

        $scanneds = $this->regionList->map(function ($item, $key) use ($crawlerISPAvg) {

            $item->isp = $this->ispMappingKey[$item->isp] ?? null;
            $item->location == 'All' ? $item->location = 'all' : $item->location;

            $scanned = new \stdClass();

            $scanned->latency = null;
            $scanned->location_networks = $item;
            
            if($item->country_id > 1){
                return $scanned;
            }
            
            if($item->isp == 'all' && $item->location == 'all'){
                $scanned->latency = $this->getAllListAvg();     // All isp 和 location
                return $scanned;
            }

            if($item->isp != 'all' && $item->location == 'all'){
                $scanned->latency = $crawlerISPAvg->isEmpty() ? null : $crawlerISPAvg->get($item->isp); //取特定的 isp 所有平均
            }else if($item->isp != 'all' && $item->location != 'all'){
                $scanned->latency = $this->getRegionIsp($item->location,$item->isp);//取特定的 isp 和 特定 location
            }

            
            return $scanned;
        });

        return $scanneds;
    }

    /**
     * 跟 Abstract 一樣只是拿掉 mapping
     *
     * @param string $regionName
     * @param string $ispName
     * @return void
     */
    protected function getRegionIsp(string $regionName, string $ispName)
    {
        if (!isset($this->listData[$ispName])) {
            return null;
        }

        return $this->listData[$ispName][strtolower($regionName)] ?? null;
    }
}