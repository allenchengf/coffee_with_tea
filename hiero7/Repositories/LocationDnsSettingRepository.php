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

    public function checkCdnIdExist($domainId,$locationId)
    {
        $result = $this->locationDnsSetting->where('domain_id',$domainId)->where('location_networks_id',$locationId)->pluck('cdn_id')->first();

        return $result ? true : false;
    }

    public function getCdnIdByLocationNetworksId($domainId,$locationId)
    {
        return $this->locationDnsSetting->where('domain_id',$domainId)->where('location_networks_id',$locationId)->pluck('cdn_id')->first();
    }

    public function getLocationNetworkId($continentId,$countryId,$networkId)
    {
        return $this->locationNetwork->where('continent_id',$continentId)->where('country_id',$countryId)
                                    ->where('network_id',$networkId)->pluck('id')->first();
    }

    public function getByLocationeNetworkRid($domainId,$rid)
    {
        return $this->locationDnsSetting->where('location_networks_id',$rid)->where('domain_id',$domainId)->first();
    }

    public function createSetting($data,$domainId)
    {
        return $this->locationDnsSetting->insert([
            'pod_record_id' => $data['pod_id'],
            'location_networks_id' => $data['location_networks_id'],
            'cdn_id' => $data['cdn_id'],
            'domain_id' => $domainId,
            'edited_by' =>$data['edited_by'],
            'created_at' => \Carbon\Carbon::now()
        ]);
    }

    public function updateLocationDnsSetting($data,$domainId,$rid)
    {
        $result = $this->locationDnsSetting->where('location_networks_id',$rid)->where('domain_id',$domainId)->update([
            'cdn_id' => $data['cdn_id'],
            'edited_by' =>$data['edited_by']
        ]);

        return $result ? true : false;
    }

    public function checkCdnSetting($domainId,$cdnId)
    {
        return $this->cdn->where('id',$cdnId)->where('domain_id',$domainId)->first();
    }

    public function getCdnCname($domainId,$cdnId)
    {
        return $this->cdn->where('id',$cdnId)->pluck('cname')->first();
    }

    public function getPodId($domainId,$rid)
    {
        return $this->locationDnsSetting->where('location_networks_id',$rid)->where('domain_id',$domainId)->pluck('pod_record_id')->first();
    }
}