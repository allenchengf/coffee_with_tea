<?php
namespace Hiero7\Services;

use Hiero7\Enums\DbError;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Repositories\ContinentRepository;
use Hiero7\Repositories\CountryRepository;
use Hiero7\Repositories\NetworkRepository;
use Hiero7\Repositories\CdnRepository;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;
    protected $continentRepository;
    protected $countryRepository;
    protected $networkRepository;
    protected $cdnRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository,
                                ContinentRepository $continentRepository, CountryRepository $countryRepository,
                                NetworkRepository $networkRepository, CdnRepository $cdnRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->continentRepository = $continentRepository;
        $this->countryRepository = $countryRepository;
        $this->networkRepository = $networkRepository;
        $this->cdnRepository = $cdnRepository;
    }

    public function getAll($domain)
    {
        $data = $this->getLocationSetting();
        for ($i=0 ; $i < count($data) ; $i++)
        {
            $data[$i]->cdn_id = $this->locationDnsSettingRepository->getDnsSetting($domain,$data[$i]->id);
            if($data[$i]->cdn_id == null){
                $data[$i]->cdn_name = $this->locationDnsSettingRepository->getDefaultCdnProvider($domain);
            }else{
                $data[$i]->cdn_name = $this->locationDnsSettingRepository->getCdnProvider($domain,$data[$i]->cdn_id);
            }
        }

        return $data;
    }

    public function getLocationSetting()
    {
        $data = $this->locationDnsSettingRepository->getLocationSetting();

        $data->map(function ($item) {
            if($item){
                $item->continent_name = $this->continentRepository->getContinentName($item['continent_id']);
                $item->country_name = $this->countryRepository->getCountryName($item['country_id']);
                $item->network_name = $this->networkRepository->getNetworkName($item['network_id']);
            }
            return $item;
        })->all();

        return $data;
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
        $data = $this->changedata($data,$domain);
        if ($checkCdnSetting)
        {
            $result = $this->locationDnsSettingRepository->updateByRid($data,$domain,$rid);
        }else{
            return false;
        }

        return $result;
    } 

    public function getLocationId($data)
    {
        return $this->locationDnsSettingRepository->getLocationId($data['continent_id'],$data['country_id'],$data['network_id']);
    }

    public function createSetting($data,$domain)
    {
        try{
            $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            $data = $this->changedata($data,$domain);
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
    }

    public function changedata($data,$domain)
    {
        $newdata = [];
        for ($i =0 ; $i < count($data) ; $i ++)
        {
            $newdata['domain_id'] = $domain;
            $newdata['cdn_id'] = $data['cdn_id'];
            $newdata['location_networks_id'] = $this->getLocationId($data);
            $newdata['edited_by'] = $data['edited_by'];
        }

        return $newdata;
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
