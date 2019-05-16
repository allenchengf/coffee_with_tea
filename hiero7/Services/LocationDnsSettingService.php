<?php
namespace Hiero7\Services;

use Hiero7\Enums\DbError;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Repositories\ContinentRepository;
use Hiero7\Repositories\CountryRepository;
use Hiero7\Repositories\NetworkRepository;
use League\Fractal;
use League\Fractal\Manager;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;
    protected $continentRepository;
    protected $countryRepository;
    protected $networkRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository,
                                ContinentRepository $continentRepository, CountryRepository $countryRepository,
                                NetworkRepository $networkRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->continentRepository = $continentRepository;
        $this->countryRepository = $countryRepository;
        $this->networkRepository = $networkRepository;
    }

    public function getAll($domain)
    {
        $locationNetworkData = $this->getLocationNetworkSetting();
        for ($i=0 ; $i < count($locationNetworkData) ; $i++)
        {
            if($this->locationDnsSettingRepository->checkCdnIdExit($domain,$locationNetworkData[$i]->id)){
                $locationNetworkData[$i]->cdn_id = $this->locationDnsSettingRepository->getCdnId($domain,$locationNetworkData[$i]->id);
                $locationNetworkData[$i]->cdn_name = $this->locationDnsSettingRepository->getCdnProvider($domain,$locationNetworkData[$i]->cdn_id);
            }else{
                $locationNetworkData[$i]->cdn_id = $this->locationDnsSettingRepository->getDefaultCdnProvider($domain)->id;
                $locationNetworkData[$i]->cdn_name = $this->locationDnsSettingRepository->getDefaultCdnProvider($domain)->name;
            }
        }

        return $locationNetworkData;
    }

    public function getLocationNetworkSetting()
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

    public function checkExit($domain,$locationNetworkRid)
    {
        $result = $this->locationDnsSettingRepository->getByLocationeNetworkRid($domain,$locationNetworkRid);

        return $result ? true : false;
    }

    public function updateSetting($data,$domain,$locationDnsRid)
    {
        $data['cdn_id']= $this->locationDnsSettingRepository->getCdnIdByCdnName($domain,$data['cdn_name']);
        $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
        if ($checkCdnSetting)
        {
            $result = $this->locationDnsSettingRepository->updateLocationDnsSetting($data,$domain,$locationDnsRid);
        }else{
            return false;
        }

        return $result;
    } 

    public function createSetting($data,$domain)
    {
        try{
            $data['cdn_id']= $this->locationDnsSettingRepository->getCdnIdByCdnName($domain,$data['cdn_name']);
            $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            $data = $this->changedata($data,$domain);
            if ($checkCdnSetting)
            {
                $result = $this->locationDnsSettingRepository->createSetting($data,$domain);
            }else{
                return false;
            }
        } catch (\Exception $e){
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
            $newdata['location_networks_id'] = $this->getLocationNetworkId($data);
            $newdata['edited_by'] = $data['edited_by'];
        }

        return $newdata;
    }

    private function getLocationNetworkId($data)
    {
        return $this->locationDnsSettingRepository->getLocationNetworkId($data['continent_id'],$data['country_id'],$data['network_id']);
    }

    private function checkCdnSetting($domian, $cdnId)
    {
        $result = $this->locationDnsSettingRepository->checkCdnSetting($domian,$cdnId);

        return $result ? true : false;
    }
}
