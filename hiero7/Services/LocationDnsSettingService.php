<?php
namespace Hiero7\Services;

use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Repositories\ContinentRepository;
use Hiero7\Repositories\CountryRepository;
use Hiero7\Repositories\NetworkRepository;
use League\Fractal;
use League\Fractal\Manager;
use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;
use Hiero7\Services\DnsProviderService;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;
    protected $continentRepository;
    protected $countryRepository;
    protected $networkRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository,
                                ContinentRepository $continentRepository, CountryRepository $countryRepository,
                                NetworkRepository $networkRepository,DnsProviderService $dnsProviderService)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->continentRepository = $continentRepository;
        $this->countryRepository = $countryRepository;
        $this->networkRepository = $networkRepository;
        $this->dnsProviderService = $dnsProviderService;
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
                if($this->locationDnsSettingRepository->getDefaultCdnProvider($domain)){
                    $locationNetworkData[$i]->cdn_id = $this->locationDnsSettingRepository->getDefaultCdnProvider($domain)->id;
                    $locationNetworkData[$i]->cdn_name = $this->locationDnsSettingRepository->getDefaultCdnProvider($domain)->name;
                }else{
                    $locationNetworkData[$i]->cdn_id = null;
                    $locationNetworkData[$i]->cdn_name = null;
                }
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

        if ($checkCdnSetting){

            $podData = $this->formatData($data,$domain,$locationDnsRid,'update');
            $podResult = $this->dnsProviderService->editRecord([
                'sub_domain' => $podData['domain_cname'],
                'value'      => $podData['cdn_cname'],
                'record_id' => $podData['record_id'],
                'record_line' => $podData['network_name'],
                'record_line' => $podData['network_name'],
            ]);
            if($podResult['message']=='Success'){
                $result = $this->locationDnsSettingRepository->updateLocationDnsSetting($data,$domain,$locationDnsRid);
            }else{
                return 'error';
            }

        }else{
            return false;
        }

        return $result;
    } 

    public function createSetting($data,$domain,$locationDnsRid)
    {
        try{
            $data['cdn_id']= $this->locationDnsSettingRepository->getCdnIdByCdnName($domain,$data['cdn_name']);

            $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            $data = $this->formatData($data,$domain,$locationDnsRid,'create');

            if ($checkCdnSetting){
                $podResult = $this->dnsProviderService->createRecord([
                    'sub_domain' => $data['domain_cname'],
                    'value'      => $data['cdn_cname'],
                    'record_line' => $data['network_name']
                ]);

                if($podResult['message'] == 'Success'){
                    $data['pod_id'] = $podResult['data']['record']['id'];
                    $result = $this->locationDnsSettingRepository->createSetting($data,$domain);
                    
                    return $result;

                }else{
                    return 'error';
                }
            }else{
                return false;
            }
        } catch (\Exception $e){
            return false;
        }

    }

    public function formatData($data,$domain,$locationDnsRid,$type)
    {
        $domainModle = new Domain;
        $cdn = new Cdn;
        $newdata = [];

        if($type == 'create'){
            for ($i =0 ; $i < count($data) ; $i ++)
            {
                $newdata['domain_id'] = $domain;
                $newdata['domain_cname'] = $domainModle->where('id',$domain)->pluck('cname')->first();
                $newdata['cdn_id'] = $data['cdn_id'];
                $newdata['cdn_cname'] = $cdn->where('id',$data['cdn_id'])->pluck('cname')->first();
                $newdata['network_name'] = $data['network_name'];
                $newdata['location_networks_id'] = $this->getLocationNetworkId($data);
                $newdata['edited_by'] = $data['edited_by'];
            }
        }else{
            for ($i =0 ; $i < count($data) ; $i ++)
            {
                $newdata['domain_cname'] = $domainModle->where('id',$domain)->pluck('cname')->first();
                $newdata['cdn_cname'] = $cdn->where('id',$data['cdn_id'])->pluck('cname')->first();
                $newdata['network_name'] = $data['network_name'];
                $newdata['record_id'] = $this->getPodId($locationDnsRid,$domain);
            }
        }


        return $newdata;
    }

    private function getPodId($data,$domain)
    {
        return $this->locationDnsSettingRepository->getPodId($domain,$data);
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
