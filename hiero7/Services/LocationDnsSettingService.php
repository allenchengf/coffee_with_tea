<?php
namespace Hiero7\Services;

use Hiero7\Enums\InputError;
use Hiero7\Repositories\LocationDnsSettingRepository;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
    }

    public function getAll()
    {
        return $this->locationDnsSettingRepository->getAll();
    }

    public function updateBySettingId($data,$setting)
    {
        return $this->locationDnsSettingRepository->update($data,$setting);
    }

    public function createSetting($data)
    {
        $this->locationDnsSettingRepository->createSetting($data);
        // 要打 pod api 獲得 podid 放入 DB
        return $this->locationDnsSettingRepository->updatePodId($podId);
    }
}
