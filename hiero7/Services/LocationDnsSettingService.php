<?php
namespace Hiero7\Services;

use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;
use Hiero7\Models\Network;
use Hiero7\Services\DnsProviderService;
use Hiero7\Repositories\LineRepository;

class LocationDnsSettingService
{
    protected $locationDnsSettingRepository;

    public function __construct(LocationDnsSettingRepository $locationDnsSettingRepository,DnsProviderService $dnsProviderService,
                                LineRepository $lineRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->dnsProviderService = $dnsProviderService;
        $this->lineRepository = $lineRepository;

    }

    public function getAll($domainId)
    {
        $cdn = new Cdn;
        $line = $this->lineRepository->getLinesById();
        $lineCollection = collect($line);

        foreach($lineCollection as $lineModel)
        {
            if($cdn->all()->isEmpty()){
                $lineModel->setAttribute('cdn', null);
                continue;
            }

            if($this->locationDnsSettingRepository->getAll()->isEmpty()){
                $cdn = $cdn->select('id','name')->where('domain_id',$domainId)->where('default',1)->first();
                $lineModel->setAttribute('cdn', $cdn);
                continue;
            }
            
            $this->getDnsSettingAll($lineModel,$cdn,$domainId,$lineModel->locationDnsSetting()->where('domain_id',$domainId)->first());
        }

        return $lineCollection;
    }

    public function checkExistDnsSetting($domainId,$locationNetworkRid)
    {
        $result = $this->locationDnsSettingRepository->getByLocationNetworkRid($domainId,$locationNetworkRid);

        return $result ? true : false;
    }

    public function updateSetting($data,$domainId,$locationDnsRid)
    {
        if(!$this->checkCdnSetting($domainId,$data['cdn_id'])){
            return false;
        }

        $podData = $this->formatData($data,$domainId,$locationDnsRid,'update');
        $podResult = $this->dnsProviderService->editRecord([
            'sub_domain' => $podData['domain_cname'],
            'value'      => $podData['cdn_cname'],
            'record_id' => $podData['record_id'],
            'record_line' => $podData['network_name'],
        ]);

        if($podResult['errorCode']){
            return 'error';
        }

        $result = $this->locationDnsSettingRepository->updateLocationDnsSetting($data,$domainId,$locationDnsRid);

        return $result;
    } 

    public function createSetting($data,$domainId,$locationNetworkRid)
    {
        try{
            if(!$this->checkCdnSetting($domainId,$data['cdn_id'])){
                return false;
            }

            if(!$this->checkLocationNetwork($data,$locationNetworkRid)){
                return false;
            }

            $formatData = $this->formatData($data,$domainId,$locationNetworkRid,'create');

            $podResult = $this->dnsProviderService->createRecord([
                'sub_domain' => $formatData['domain_cname'],
                'value'      => $formatData['cdn_cname'],
                'record_line' => $formatData['network_name']
            ]);

            if($podResult['errorCode']){
                return 'error';
            }
            
            $formatData['pod_id'] = $podResult['data']['record']['id'];
            
            return $this->locationDnsSettingRepository->createSetting($formatData,$domainId);

        } catch (\Exception $e){
            return false;
        }
    }

    public function formatData($data,$domainId,$locationNetworkRid,$type)
    {
        $domainModel = new Domain;
        $networkModel = new Network;
        $newData = [];

        for($i =0 ; $i < count($data) ; $i ++)
        {
            $newData['domain_cname'] = $domainModel->where('id',$domainId)->pluck('cname')->first();
            $newData['cdn_cname'] = $this->locationDnsSettingRepository->getCdnCname($data['cdn_id']);
            $newData['network_name'] = $networkModel->where('id', $data['network_id'])->pluck('name')->first();

            if($type == 'create'){
                $newData['domain_id'] = $domainId;
                $newData['cdn_id'] = $data['cdn_id'];
                $newData['location_networks_id'] = $locationNetworkRid;
                $newData['edited_by'] = $data['edited_by'];

            }else{
                $newData['record_id'] = $this->getPodId($locationNetworkRid,$domainId);
            }
        }

        return $newData;
    }

    private function getDnsSettingAll($lineModel,$cdn,$domainId,$locationSetting)
    {
        if(!$locationSetting){
            $cdn = $cdn->select('id','name')->where('domain_id',$domainId)->where('default',1)->first();
            $lineModel->setAttribute('cdn', $cdn);        
            
            return $lineModel;
        }

        $cdn = $locationSetting->cdn()->select('id','name')->first();
        $lineModel->setAttribute('cdn', $cdn);
        
        return $lineModel;
    }

    private function getPodId($data,$domainId)
    {
        return $this->locationDnsSettingRepository->getPodId($domainId,$data);
    }

    private function checkLocationNetwork($data, $locationNetworkRid)
    {
        $result = $this->locationDnsSettingRepository->getLocationNetworkId($data['continent_id'],$data['country_id'],$data['network_id']);

        return $result == $locationNetworkRid ? true : false;
    }

    private function checkCdnSetting($domainId, $cdnId)
    {
        $result = $this->locationDnsSettingRepository->checkCdnSetting($domainId,$cdnId);

        return $result ? true : false;
    }
}
