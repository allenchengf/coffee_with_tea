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
use Hiero7\Models\Network;
use Hiero7\Services\DnsProviderService;
use Hiero7\Repositories\LineRepository;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository,DnsProviderService $dnsProviderService,
                                LineRepository $line)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->dnsProviderService = $dnsProviderService;
        $this->line = $line;

    }

    public function getAll($domain)
    {
        $cdn = new Cdn;
        $line = $this->line->getLinesById();
        $lineCollection = collect($line);
        foreach($lineCollection as $v)
        {
            if($this->locationDnsSettingRepository->checkCdnIdExit($domain,$v['id'])){
                $cdnId = $this->locationDnsSettingRepository->getCdnIdByLocationNetworksId($domain,$v['id']);
                $cdn = $cdn->select('id','name')->where('domain_id',$domain)->where('id',$cdnId)->first();
                $v->setAttribute('cdn_id', $cdn->id);
                $v->setAttribute('cdn', $cdn);
            }else{
                if ($cdn->all()->isempty()){
                    $v->setAttribute('cdn_id', null);
                }else{
                    $cdn = $cdn->select('id','name')->where('domain_id',$domain)->where('default',1)->first();
                    $v->setAttribute('cdn_id', $cdn->id);
                    $v->setAttribute('cdn', $cdn);
                }
            }
        }

        return $lineCollection;
    }

    public function checkExitDnsSetting($domain,$locationNetworkRid)
    {
        $result = $this->locationDnsSettingRepository->getByLocationeNetworkRid($domain,$locationNetworkRid);

        return $result ? true : false;
    }

    public function updateSetting($data,$domain,$locationDnsRid)
    {
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

    public function createSetting($data,$domain,$locationNetworkRid)
    {
        try{
            $checkCdnSetting = $this->checkCdnSetting($domain,$data['cdn_id']);
            $checkLocationNetwork = $this->checkLocationNetwork($data,$locationNetworkRid);

            if ($checkCdnSetting && $checkLocationNetwork){
                $data = $this->formatData($data,$domain,$locationNetworkRid,'create');

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

    public function formatData($data,$domain,$locationNetworkRid,$type)
    {
        $domainModle = new Domain;
        $network = new Network;
        $newdata = [];

        if($type == 'create'){

            for ($i =0 ; $i < count($data) ; $i ++)
            {
                $newdata['domain_id'] = $domain;
                $newdata['domain_cname'] = $domainModle->where('id',$domain)->pluck('cname')->first();
                $newdata['cdn_id'] = $data['cdn_id'];
                $newdata['cdn_cname'] = $this->locationDnsSettingRepository->getCdnCname($domain,$data['cdn_id']);
                $newdata['network_name'] = $network->where('id', $data['network_id'])->pluck('name')->first();
                $newdata['location_networks_id'] = $locationNetworkRid;
                $newdata['edited_by'] = $data['edited_by'];
            }
        }else{

            for ($i =0 ; $i < count($data) ; $i ++)
            {
                $newdata['domain_cname'] = $domainModle->where('id',$domain)->pluck('cname')->first();
                $newdata['cdn_cname'] = $this->locationDnsSettingRepository->getCdnCname($domain,$data['cdn_id']);
                $newdata['network_name'] = $network->where('id', $data['network_id'])->pluck('name')->first();
                $newdata['record_id'] = $this->getPodId($locationNetworkRid,$domain);
            }
            
        }

        return $newdata;
    }

    private function getPodId($data,$domain)
    {
        return $this->locationDnsSettingRepository->getPodId($domain,$data);
    }

    private function checkLocationNetwork($data, $locationNetworkRid)
    {
        $result = $this->locationDnsSettingRepository->getLocationNetworkId($data['continent_id'],$data['country_id'],$data['network_id']);

        return $result == $locationNetworkRid ? true : false;
    }

    private function checkCdnSetting($domian, $cdnId)
    {
        $result = $this->locationDnsSettingRepository->checkCdnSetting($domian,$cdnId);

        return $result ? true : false;
    }
}
