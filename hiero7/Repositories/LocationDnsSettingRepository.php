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

    public function getAllSetting($domainId)
    { //取 Allen 表格 和自己設定的
        $origin = $this->getLocationSetting; 
        $custom = $this->getDnsSetting($domainId);

    }

    public function getDnsSetting($domainId)
    {
        return $this->locationDnsSetting->with(['cdns','locations'])->where('domain_id',$domainId)->get();
    }

    public function getLocationSetting()
    {
        return $this->locationNetwork->with(['network','continent','country'])->get(); //取 Default 設定好的
    }

    public function getCdnProvider($domainId,$default)
    {
        return $this->cdn->where('domain_id',$domainId)->where('default',$default)->get();
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
            'created_at' => \Carbon\Carbon::now()
        ]);
    }

    public function updateByRid($data,$domainId,$rid)
    {
        $result = $this->locationDnsSetting->where('id',$rid)->where('domain_id',$domainId)->update($data);

        return $result ? true : false;
    }

    public function checkCdnSetting($domainId,$cdnId)
    {
        return $this->cdn->where('id',$cdnId)->where('domain_id',$domainId)->get();
    }
}