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
                $domain_id = $this->domainRepository->store($domain, $user);
                if(!is_int($domain_id))
                    throw $domain_id;
            } catch (\Exception $e) {
                $record = $this->domainRepository->getDomainIdIfExist($domain["name"], $user["user_group_id"]);
                if($record->count() == 1){
                    $domain_id = $record->id;                    
                }else{
                    array_push($errors, $e->getMessage());
                    $domain_added = false;
                }
            }

            if($domain_added){
                foreach($domain["cdns"] as $key => $cdn){
                    try {
                        $add_cdn_result = $this->cdnRepository->store($cdn, $domain_id, $user, $key);
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