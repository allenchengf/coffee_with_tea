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
        $this->locationDnsSettingRepository->getDnsSetting($domain);

    }

    public function getByRid($domain,$rid)
    {
        $result = $this->locationDnsSettingRepository->getByRid($domain,$rid);

        try{
            $result = $result[0];
        } catch(\Exception $e)
        {
            return false;
        }
        return true;
    }

    public function updateSetting($data,$domain,$rid)
    {
        $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            
        if ($checkCdnSetting)
        {
            $result = $this->locationDnsSettingRepository->updateByRid($data,$domain,$rid);
        }else{
            return false;
        }

        return $result;
    } 

    public function createSetting($data,$domain)
    {
        try{
            $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            
            if ($checkCdnSetting)
            {
                $result = $this->locationDnsSettingRepository->createSetting($data,$domain);
            }else{
                return false;
            }

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
        try{
            $result = $result[0];
        } catch(\Exception $e)
        {
            return false;
        }
        return $result->pod_record_id;
    }

    public function checkCdnSetting($domian, $cdnId)
    {
        $result = $this->locationDnsSettingRepository->checkCdnSetting($domian,$cdnId);
        try{
            $result = $result[0];
        } catch(\Exception $e)
        {
            return false;
        }
        return true;
    }
}
