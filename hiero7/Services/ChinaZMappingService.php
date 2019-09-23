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

            if($item->country_id > 1){
                $scanned->latency = 0; //問 leo 非中國 要回傳什麼
            }

            if($item->isp == 'all' && $item->location != 'all'){
                $scanned->latency = null; //Taiwan / Hong kong / Macao
            }

            if($item->isp == 'all' && $item->location == 'all' && $item->country_id == 1){
                $scanned->latency = 'Leo function'; //所有 isp 和 location  ;
            }
dd($crawlerISPTime);
            if($item->isp != 'all' && $item->location == 'all'){

                $scanned->latency = $crawlerISPTime->get($item->isp)->avg(); //取特定的 isp 所有平均 ;
            }

            if($item->isp != 'all' && $item->location != 'all'){
                $scanned->latency = $crawlerISPTime->get($item->isp)->pluck($item->location); //取特定的 isp 和 特定 location;
            }

            $item->continent;
            $item->country;
            $item->network;

            $scanned->location_networks = $item;

            return $scanned;
        });

        return $scanneds;
    }
}