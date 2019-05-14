<?php

namespace Hiero7\Repositories;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Models\LocationNetwork;
use Hiero7\Enums\DbError;
use Hiero7\Enums\InputError;

class LocationDnsSettingRepository
{

    protected $locationDnsSetting;
    protected $locationNetwork;

    public function __construct(LocationDnsSetting $locationDnsSetting,LocationNetwork $locationNetwork)
    {
        $this->locationDnsSetting = $locationDnsSetting;
        $this->locationNetwork = $locationNetwork;
    }

    public function getAll($domain)
    {
        $result = $this->locationDnsSetting->with(['cdns','locations'])->where('domain_id',$domain)->get();
        if ($result->count())
        {
            $origin = $this->locationNetwork->with(['network','continent','country'])->get();
            // foreach($result as $result)
            // {
            //     $result->cdn_cname = $test->cdns->name;
            // }
            return $result;
        }else{
            return $this->locationNetwork->with(['network','continent','country'])->get(); //取 Default 設定好的
        }
    }

    public function getById($domain,$rid)
    {
        return $this->locationDnsSetting->where($rid);
    }

    public function getPodId($domian, $rid)
    {
        return $this->locationDnsSetting->select('pod_record_id')->where('id',$rid)->where('domain_id',$domian)->get();
    }

    public function createSetting($data,$domainId)
    {
        return $this->locationDnsSetting->insert([
            'pod_record_id' => 123244,
            'location_networks_id' => $data->location_networks_id,
            'cdn_id' => $data->cdn_id,
            'domain_id' => $domainId
        ]);
    }

    public function updatePodId($settingid,$podid)
    {
        return $this->locationDnsSetting->where('id',$settingid)->update(['pod_record_id' => $podid]);
    }

    public function updateBySettingId($data,$settingId)
    {
        return $this->locationDnsSetting->where('id',$settingid)->update($data);
    }
}