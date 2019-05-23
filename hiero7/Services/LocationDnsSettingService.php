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
            }else{
                $locationDnsSetting = $this->locationDnsSettingRepository->getAll();

                if($locationDnsSetting->isEmpty()){
                    $cdn = $cdn->select('id','name')->where('domain_id',$domainId)->where('default',1)->first();
                    $lineModel->setAttribute('cdn', $cdn);

                }else{
                    $locationSetting = $lineModel->locationDnsSetting()->first();

                    if($locationSetting){
                        $cdn = $locationSetting->cdn()->select('id','name')->first();
                        $lineModel->setAttribute('cdn', $cdn);

                    }else{
                        $cdn = $cdn->select('id','name')->where('domain_id',$domainId)->where('default',1)->first();
                        $lineModel->setAttribute('cdn', $cdn);
                        }
                    }
                }
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
        $checkCdnSetting = $this->checkCdnSetting($domainId,$data['cdn_id']);

        if ($checkCdnSetting){

            $podData = $this->formatData($data,$domainId,$locationDnsRid,'update');
            $podResult = $this->dnsProviderService->editRecord([
                'sub_domain' => $podData['domain_cname'],
                'value'      => $podData['cdn_cname'],
                'record_id' => $podData['record_id'],
                'record_line' => $podData['network_name'],
            ]);

            if($podResult['message']=='Success'){
                $result = $this->locationDnsSettingRepository->updateLocationDnsSetting($data,$domainId,$locationDnsRid);
            }else{
                return 'error';
            }
            
        }else{
            return false;
        }

        return $result;
    } 

    public function createSetting($data,$domainId,$locationNetworkRid)
    {
        try{

            $checkCdnSetting = $this->checkCdnSetting($domainId,$data['cdn_id']);
            $checkLocationNetwork = $this->checkLocationNetwork($data,$locationNetworkRid);

            if ($checkCdnSetting && $checkLocationNetwork){
                $data = $this->formatData($data,$domainId,$locationNetworkRid,'create');

                $podResult = $this->dnsProviderService->createRecord([
                    'sub_domain' => $data['domain_cname'],
                    'value'      => $data['cdn_cname'],
                    'record_line' => $data['network_name']
                ]);

                if($podResult['message'] == 'Success'){
                    $data['pod_id'] = $podResult['data']['record']['id'];
                    $result = $this->locationDnsSettingRepository->createSetting($data,$domainId);
                    
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
