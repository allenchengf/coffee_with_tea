<?php
namespace Hiero7\Services;

use Hiero7\Enums\DbError;
use Hiero7\Repositories\LocationDnsSettingRepository;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
    }

    public function getAll($domain)
    {

        return $this->locationDnsSettingRepository->getAll($domain);
    }

    public function getById($domain,$rid)
    {
        return $this->locationDnsSettingRepository->getById($domain,$rid);
    }

    public function updateBySettingId($data,$setting)
    {
        return $this->locationDnsSettingRepository->update($data,$setting);
    } 

    public function createSetting($data,$domainId,$rid)
    {
        try{
            $result = $this->locationDnsSettingRepository->createSetting($data,$domainId);
        } catch (\Exception $e)
        {
            return false;
        }
        return $result;
        // 要打 pod api 獲得 podid 放入 DB
        // $this->locationDnsSettingRepository->updatePodId($podId);
        // return 
    }

    public function checkPodId($domian, $rid)
    {
        $result = $this->locationDnsSettingRepository->getPodId($domian,$rid);
        return $result == null ? $result : false;
    }
}
