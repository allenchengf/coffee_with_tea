<?php
namespace Hiero7\Services;

use Hiero7\Services\DomainGroupService;
use Hiero7\Models\DomainGroupMapping;
use Hiero7\Repositories\DomainRepository;
use Exception;


class BatchGroupService{
    public function __construct(DomainGroupService $domainGroupService,DomainRepository $domainRepository)
    {
        $this->domainGroupService = $domainGroupService;
        $this->domainRepository = $domainRepository;
        
    }

    public function store($domains,$domainGroup, $user)
    {
        $success = $failure = [];

        foreach($domains as $domain){

            list($domain, $domainId, $errorMessage)  = $this->checkDomain($domain,$domainGroup, $user);

            if ($errorMessage) {
                $failure[] = ['name' => $domain["name"],
                                'errorCode' => 40000,
                                'message' => $errorMessage]; // checkDomain 沒有過就不下去執行
                continue;
            }

            $changeCdnResult = $this->domainGroupService->changeCdnDefault($domainGroup, $domainId, $user['uuid']);

            if(!$changeCdnResult){
                $failure[] = ['name' => $domain["name"],
                            'errorCode' => 5001,
                            'message' => 'Internal change CDN service error',
                            // 'message' => 'Internal service error'
                            ];
                continue;
            }

            $changeIrouteResult = $this->domainGroupService->changeIrouteSetting($domainGroup, $domainId);

            if(!$changeIrouteResult){
                $failure[]  = ['name' => $domain["name"],
                            'errorCode' => 5001,
                            'message' => 'Internal change iRoute service error', 
                            // 'message' => 'Internal service error'
                            ];
                continue;
            }

            DomainGroupMapping::create([
                'domain_id' => $domainId,
                'domain_group_id' => $domainGroup->id
            ]);
            
            $success[] = $domain["name"];
        }

        $result = ['success' => $success,
                    'failure' => $failure
                    ];
            
        return $result;
    }

    public function checkDomain($domain,$domainGroup, $user)
    {
        $domainId = null;
        $errorMessage = null;

        try{
            $domainModel = $this->domainRepository->getDomainIdIfExist($domain["name"], $user["user_group_id"]);
            if(is_null($domainModel)){
                throw new Exception("The domain is undefined.");
            }
            $domainId = $domainModel->id;
            
            $domainGroupModel = $domainGroup->mapping->where('domain_id',$domainId)->isEmpty();

            if(!$domainGroupModel){
                throw new Exception('Domain already exist at this Group.');
            }
            
            $checkDomainCdnSetting = $this->domainGroupService->compareDomainCdnSetting($domainGroup, $domainId);
            if(!$checkDomainCdnSetting){
                throw new Exception("Domain's Cdn Provider are different with Group's");
            }
        } catch  (\Exception $e){
            $errorMessage = $e->getMessage();
        }

        return  [$domain, $domainId, $errorMessage];

    }

}