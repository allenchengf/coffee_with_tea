<?php
namespace Hiero7\Services;

use Hiero7\Services\DomainGroupService;
use Hiero7\Models\DomainGroupMapping;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Enums\InputError;
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

            list($domain, $domainId, $errorCode)  = $this->checkDomain($domain,$domainGroup, $user);

            if ($errorCode) {
                $failure[] = ['name' => $domain["name"],
                                'errorCode' => $errorCode,
                                'message' => InputError::getDescription($errorCode)]; // checkDomain 沒有過就不下去執行
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
                throw new Exception(InputError::DOMAIN_IS_UNDEFINED);
            }
            $domainId = $domainModel->id;
            
            if(!$domainModel->domainGroup->isEmpty()){
                throw new Exception(InputError::DOMAIN_ALREADY_HAS_GROUP);
            }
            
            $domainGroupModel = $domainGroup->mapping->where('domain_id',$domainId)->isEmpty();
            if(!$domainGroupModel){
                throw new Exception(InputError::DOMAIN_ALREADY_EXIST_GROUP);
            }
            
            $checkDomainCdnSetting = $this->domainGroupService->compareDomainCdnSetting($domainGroup, $domainId);
            if(!$checkDomainCdnSetting){
                throw new Exception(InputError::DOMAIN_CDNPROVIDER_DIFFERENT_WITH_GROUPS);
            }
        } catch  (\Exception $e){
            $errorCode = $e->getMessage();
        }

        return  [$domain, $domainId, $errorCode];

    }

}