<?php

namespace Hiero7\Repositories;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Models\LocationNetwork;
use Hiero7\Models\Cdn;

class LocationDnsSettingRepository
{

    protected $locationDnsSetting;
    protected $locationNetwork;
    protected $cdn;

    public function __construct(LocationDnsSetting $locationDnsSetting,LocationNetwork $locationNetwork, Cdn $cdn)
    {
        $this->locationDnsSetting = $locationDnsSetting;
        $this->locationNetwork = $locationNetwork;
        $this->cdn = $cdn;
    }

    public function getDnsSetting($domainId,$locationId)
    {
        return $this->locationDnsSetting->where('domain_id',$domainId)->where('location_networks_id',$locationId)->pluck('cdn_id')->first();
    }

    public function getLocationSetting()
    {
        return $this->locationNetwork->all(); //取 Default 設定好的
    }

    public function getDefaultCdnProvider($domainId)
    {
        return $this->cdn->where('domain_id',$domainId)->where('default',1)->pluck('name')->first();
    }

    public function getCdnProvider($domainId,$cdnId)
    {
        return $this->cdn->where('domain_id',$domainId)->where('id',$cdnId)->where('default',0)->pluck('name')->first();
    }

    public function getLocationId($continentId,$countryId,$networkId)
    {
        return $this->locationNetwork->where('continent_id',$continentId)->where('country_id',$countryId)
                                    ->where('network_id',$networkId)->pluck('id')->first();
    }

    public function getByRid($domainId,$rid)
    {
        return $this->locationDnsSetting->where('location_networks_id',$rid)->where('domain_id',$domainId)->get();
    }

    public function getPodId($domainId, $rid)
    {
        return $this->locationDnsSetting->select('pod_record_id')->where('id',$rid)->where('domain_id',$domainId)->get();
    }

    public function createSetting($data,$domainId)
    {
        return $this->locationDnsSetting->insert([
            'pod_record_id' => 123244,
            'location_networks_id' => $data['location_networks_id'],
            'cdn_id' => $data['cdn_id'],
            'domain_id' => $domainId,
            'edited_by' =>$data['edited_by'],
            'created_at' => \Carbon\Carbon::now()
        ]);
    }

    public function updateByRid($data,$domainId,$rid)
    {
        $result = $this->locationDnsSetting->where('location_networks_id',$rid)->where('domain_id',$domainId)->update($data);

        return $result ? true : false;
    }

    public function checkCdnSetting($domainId,$cdnId)
    {
        return $this->cdn->where('id',$cdnId)->where('domain_id',$domainId)->get();
    }
}