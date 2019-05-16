<?php

namespace Hiero7\Services;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Services\DnsProviderService;
use DB;

class BatchService{

    protected $cdnRepository;
    protected $domainRepository;
    protected $dnsProviderService;

    public function __construct(CdnRepository $cdnRepository,
        DnsProviderService $dnsProviderService,
        DomainRepository $domainRepository){
        $this->dnsProviderService = $dnsProviderService;
        $this->cdnRepository = $cdnRepository;
        $this->domainRepository = $domainRepository;
    }

    public function store($domains, $user){
        $errors = [];
        foreach($domains as $domain){
            $domain_added = true;
            $append = false;
            try {
                $domain_id = $this->domainRepository->store($domain, $user);
                if(!is_int($domain_id))
                    throw $domain_id;
            } catch (\Exception $e) {
                $record = $this->domainRepository->getDomainIdIfExist($domain["name"], $user["user_group_id"]);

                if($record->exists()){
                    $append = true;
                    $domain_id = $record->id;
                }else{
                    array_push($errors, $e->getMessage());
                    $domain_added = false;
                }
            }

            if($domain_added){
                foreach($domain["cdns"] as $key => $cdn){
                    DB::beginTransaction();
                    try {
                        $cdn["ttl"] = $cdn["ttl"]??env("CDN_TTL");
                        if($key === 0 && !$append){
                            $dnsPodResponse = $this->dnsProviderService->createRecord(
                                [
                                    'sub_domain' => $domain["name"],
                                    'value'      => $cdn["cname"],
                                    'ttl'        => $cdn["ttl"],
                                    'status'     => true
                                ]);
                            if (!is_null($dnsPodResponse['errorCode']) || array_key_exists('errors',
                                    $dnsPodResponse))
                                throw new \Exception($dnsPodResponse['message']." for ".$cdn["cname"], $dnsPodResponse['errorCode']);
                        }
                        $cdn["dns_provider_id"] = $dnsPodResponse['data']['record']['id']??0;
                        $cdn["default"] = !$append&&$key===0?1:0;
                        $add_cdn_result = $this->cdnRepository->store($cdn, $domain_id, $user, $key);
                        if(!is_int($add_cdn_result))
                            throw $add_cdn_result;
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        array_push($errors, $e->getMessage());
                    }
                }                
            }
        }
        return $errors;
    }
}