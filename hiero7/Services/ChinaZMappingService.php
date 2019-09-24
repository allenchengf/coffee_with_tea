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
    }
    
    public function mappingData()
    { 
        $crawlerISPTime = $this->filterData($this->crawlerData['results'] ?? []); // Leo 的結果整理

        $scanneds = $this->regionList->map(function ($item, $key) use ($crawlerISPTime) {

            $item->isp = $this->ispMappingKey[$item->isp];

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
            
            if ($crawlerISPTime->has($item->isp)){
    
                if($item->isp != 'all' && $item->location == 'all'){
                    $scanned->latency = $crawlerISPTime->get($item->isp)->avg(); //取特定的 isp 所有平均
                }else if($item->isp != 'all' && $item->location != 'all'){
                    $scanned->latency = $crawlerISPTime->get($item->isp)->pluck($item->location); //取特定的 isp 和 特定 location
                }
            
            }
            
            return $scanned;
        });

        return $scanneds;
    }
}