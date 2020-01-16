<?php
namespace Hiero7\Services;

use Hiero7\Services\DomainGroupService;
use Hiero7\Models\DomainGroupMapping;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Enums\InputError;
use Exception;
use Hiero7\Models\Domain;
use Hiero7\Traits\OperationLogTrait;


class BatchGroupService{
    use OperationLogTrait;

    public function __construct(DomainGroupService $domainGroupService,DomainRepository $domainRepository)
    {
        $this->domainGroupService = $domainGroupService;
        $this->domainRepository = $domainRepository;
        $this->setCategory(config('logging.category.domain_group'));
        
    }

    public function store($domains,$domainGroup, $user)
    {
        $domainSuccess = $domainFailure = [];

        foreach($domains as $domain){

            list($domain, $domainId, $errorCode)  = $this->checkDomain($domain,$domainGroup, $user);

            if ($errorCode) {
                $domainFailure[] = ['name' => $domain["name"],
                                    'errorCode' => (int)$errorCode,
                                    'message' => InputError::getDescription($errorCode)]; // checkDomain 沒有過就不下去執行
                continue;
            }

            $changeCdnResult = $this->domainGroupService->changeCdnDefault($domainGroup, $domainId, $user['uuid']);

            if(!$changeCdnResult){
                $domainFailure[] = ['name' => $domain["name"],
                                    'errorCode' => 5001,
                                    'message' => 'Internal change CDN service error',
                                    // 'message' => 'Internal service error'
                                    ];
                continue;
            }

            $changeIrouteResult = $this->domainGroupService->changeIrouteSetting($domainGroup, $domainId);

            if(!$changeIrouteResult){
                $domainFailure[]  = ['name' => $domain["name"],
                                    'errorCode' => 5001,
                                    'message' => 'Internal change iRoute service error', 
                                    // 'message' => 'Internal service error'
                                    ];
                continue;
            }

            $domainGroupMapping = DomainGroupMapping::create([
                'domain_id' => $domainId,
                'domain_group_id' => $domainGroup->id
            ]);

            $this->setChangeTo($domainGroupMapping->saveLog())->createOperationLog();
            
            $domainSuccess[] = ['name'=> $domain["name"]];
        }

        $result = ['success' => ['domain'=> $domainSuccess],
                    'failure' => ['domain'=> $domainFailure]
                    ];
            
        return $result;
    }

    /**
     *  檢查 Domain 是否合格
     * 
     * 條件一: 
     * 條件二:
     *
     * @param [type] $domain
     * @param [type] $domainGroup
     * @param [type] $user
     * @return void
     */
    public function checkDomain($domain,$domainGroup, $user)
    {
        $domainId = null;
        $errorCode = null;
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
            
            $targetDomain = Domain::find($domainId);
            if(!$targetDomain){
                return $this->setStatusCode(400)->response($this->message, InputError::DOMAIN_NOT_EXIST, []);
            }

            $checkDomainCdnSetting = $this->domainGroupService->compareDomainCdnSetting($domainGroup, $targetDomain);
            if(!$checkDomainCdnSetting){
                throw new Exception(InputError::DOMAIN_CDNPROVIDER_DIFFERENT_WITH_GROUPS);
            }
        } catch  (\Exception $e){
            $errorCode = $e->getMessage();
        }

        return  [$domain, $domainId, $errorCode];

    }

}