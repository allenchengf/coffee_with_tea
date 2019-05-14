<?php

namespace Hiero7\Repositories;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Enums\DbError;
use Hiero7\Enums\InputError;

class LocationDnsSettingRepository
{

    protected $locationDnsSetting;

    public function __construct(LocationDnsSetting $locationDnsSetting)
    {
        $this->locationDnsSetting = $locationDnsSetting;
    }

    public function getAll()
    {
        return $this->locationDnsSetting->all();
    }

    public function createSetting($data)
    {
        return $this->locationDnsSetting->created($data);
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