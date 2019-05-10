<?php

namespace Hiero7\Services;
use Hiero7\Enums\InputError;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\DomainRepository;

class BatchService{
    protected $cdnRepository;
    protected $domainRepository;


    public function __construct(CdnRepository $cdnRepository,
        DomainRepository $domainRepository){
        $this->cdnRepository = $cdnRepository;
        $this->domainRepository = $domainRepository;
    }

    public function store($domains, $user){

        $errors = [];
        foreach($domains as $domain){
            $domain_added = true;
            try {
                $add_domain_result = $this->domainRepository->store($domain, $user);
                if(!is_int($add_domain_result))
                    throw $add_domain_result;
            } catch (\Exception $e) {
                array_push($errors, $e->getMessage());
                $domain_added = false;
            }
            if($domain_added){
                foreach($domain["cdns"] as $cdn){
                    try {
                        $add_cdn_result = $this->cdnRepository->store($cdn, $add_domain_result, $user);
                        if(!is_int($add_cdn_result))
                            throw $add_cdn_result;
                    } catch (\Exception $e) {
                        array_push($errors, $e->getMessage());
                    }
                }                
            }
        }
        return $errors;
    }
}