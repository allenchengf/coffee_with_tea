<?php

namespace Hiero7\Services;
use Hiero7\Repositories\{CdnRepository, DomainRepository, CdnProviderRepository};
use Hiero7\Services\DnsProviderService;
use Hiero7\Enums\{InputError,InternalError};
use Hiero7\Traits\{DomainHelperTrait, OperationLogTrait};
use Illuminate\Support\Collection;
use Hiero7\Models\Job;
use Artisan;
use Exception;
use App\Jobs\AddDomainAndCdn;
use App\Jobs\CallWorker;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Redis;

class BatchService{

    use DomainHelperTrait;
    use DispatchesJobs;
    use OperationLogTrait;

    protected $cdnRepository;
    protected $domainRepository;
    protected $dnsProviderService;
    protected $cdnProviderRepository;

    public function __construct(
        CdnRepository $cdnRepository,
        DnsProviderService $dnsProviderService,
        DomainRepository $domainRepository,
        CdnProviderRepository $cdnProviderRepository)
    {
        $this->dnsProviderService = $dnsProviderService;
        $this->cdnRepository = $cdnRepository;
        $this->domainRepository = $domainRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
    }

    /**
     * 和 job 裡面的邏輯一樣，這邊方便 重構 和 debug
     * 
     * 情境一： 新增 domain 和 cdn 
     *         成功 =>  只有 domain 成功 success 會有 domain name。兩個都成功 success 會有 domain name 和 cdn name
     *         失敗 =>  只有 domain 失敗，邏輯就不處理 cdn， failure 只會有 domain 的 errorCode 。
     *                  但 domain 如果是已經存在了，邏輯會處理 cdn， failure 會有 domain 的 errorCode。 
     * 
     * 情境二： 新增 domain 無 cdn ，此情境目前沒有接。但有把它註解起來。
     *
     * @param [type] $domains
     * @param [type] $user
     * @return void
     */
    public function store($domains, $user)
    {
        $success = $failure = [];

        // Job 存 Operation Log 資訊
        $operationLogInfo = [
            'jwtToken' => $this->getJWTToken(),
            'jwtPayload' => $this->getJWTPayload(),
            'ip' => $this->getClientIp(),
        ];

        // 批次新增 domain 迴圈
        foreach ($domains as $domain) {
            $domainError = $cdnSuccess = $cdnError =[];
            // 新增或查詢已存在 domain
            list($domain, $domain_id, $errorMessage, $errorCode) = $this->storeDomain($domain, $user, $operationLogInfo);

            if (! is_null($errorCode)||! is_null($errorMessage)) {
                $domainError = [
                    'name' => $domain["name"],
                    'errorCode' => $errorCode,
                    'message' => InputError::getDescription($errorCode),
                    // 目前不接 domain 沒有給 cdn 的 error 所以先註解起來摟～
                    // 'errorCode' => !is_null($errorCode)? $errorCode:111,
                    // 'message' => !is_null($errorCode)?InputError::getDescription($errorCode):'This domain has been stored with no cdns.',
                    'cdn' => []
                ];                
                
                if($errorCode != 4046){
                    $failure[] = $domainError;
                    continue;
                }

            }

            //會有 已經存在的 domain 處理之後的 cdn
            if(isset($domain['cdns'])){
                //處理 Cdn 的 新增 刪除
                list($cdnSuccess, $cdnError) = $this->handelCdn($user, $domain_id, $domain, $operationLogInfo);
            }

            
            if(!empty($domainError)){
                //有 Domain error
                $domainError['cdn'] = $cdnError;
                $failure[] = $domainError;
            }else{
                //無 Domain error
                if(!empty($cdnError)){
                    //有 Cdn error
                    $failure[] = ['name' => $domain['name'],
                    'errorCode' => null,
                    'message' => null ,  'cdn' => $cdnError];
                }
                //無 Cdn error
                $success[] = ['name' => $domain['name'], 'cdn' => $cdnSuccess];
                
            }

        }

        $result = ['success' => ['domain' => $success],
                    'failure' => ['domain' =>  $failure],
                    ];

        return $result;
    }

    /**
     * 負責處理 Batch 的 Cdn 部分
     *
     * @param [type] $user
     * @param [type] $domain_id
     * @param [type] $domain
     * @return void
     */
    public function handelCdn($user, $domain_id, $domain, $operationLogInfo=null)
    {
        // 取此權限全部 cdn_providers
        $myCdnProviders = collect($this->cdnProviderRepository->getCdnProvider($user["user_group_id"])->toArray());
        
        // 查詢 cdns.domain_id 是否存在 ? 不存在才打 POD，代表 POD 的 default 尚未存在
        $cdns = $this->cdnRepository->indexByWhere(['domain_id' => $domain_id]);
        $isFirstCdn = count($cdns) == 0 ? true : false;

        $cdnSuccess = $cdnError = [];
        // 批次新增 cdn 迴圈
        foreach ($domain["cdns"] as $cdn) {

            // 此次 $cdn['name'] 換 cdn_providers.id、ttl欄位
            $myCdnProviders->each(function ($v) use (&$cdn) {
                if (strcasecmp($v['name'], $cdn["name"]) == 0) { // cdn_providers.name 不區分大小寫相同
                    $cdn["cdn_provider_id"] = $v['id'];
                    $cdn["ttl"] = $v['ttl'];
                    $cdn["status"] = $v['status'] == 'active' ? true : false;
                    return false; // break;
                }
            });

            // 若此 $cdn['name'] 不匹配 cdn_providers.name
            if(! isset($cdn["cdn_provider_id"])) {
                $cdnError[] = ['name' => $cdn["name"],
                                'errorCode' => 112,
                                'message' => 'This cdn_providers.name ' . $cdn["name"]. ' doesn\'t exist.'];
                
                continue;
            }

            // 新增 cdn
            list($isFirstCdn, $errorMessage) = $this->storeCdn($domain, $domain_id, $cdn, $user, $isFirstCdn);
            if (! is_null($errorMessage)) {

                $cdnError[] = ['name' => $cdn["name"],
                                'errorCode' => 113,
                                'message' => $errorMessage];
                
                continue;
            }

            $cdnSuccess[] = ['name' => $cdn["name"]]; 
        }

        return [$cdnSuccess, $cdnError];
    }

    public function process($domains, $user, $ugId)
    {
        $queueName = 'batchCreateDomainAndCdn'.$user['uuid'].$ugId;

        //連 Redis 的 2 dataBase
        $redisJobs = Redis::connection('jobs');

        //檢查是否有原本的資料
        $this->checkProcessRecord($queueName,$redisJobs);

        // Job 存 Operation Log 資訊
        $operationLogInfo = [
            'jwtToken' => $this->getJWTToken(),
            'jwtPayload' => $this->getJWTPayload(),
            'ip' => $this->getClientIp(),
        ];

        // 批次新增 domain & cdn 迴圈， $count 記錄總共有幾筆
        $count = 0;
        foreach ($domains as $domain) {
            $count++;
            //把原邏輯 搬去 job 
            $job = (new AddDomainAndCdn($domain,$user,$queueName,$operationLogInfo))
            ->onConnection('database')
            ->onQueue($queueName.$count);

            // 這個到時候可以拿到 jobId 
            $this->dispatch($job);

            // 用 job 呼叫指令(Artisan::Call) 才不會 return 被吃掉
            // 一個 AddDomainAndCdn job 配一個 worker job 才會剛好都處理完，table 不會有殘留 worker
            // supervisor 監督的 queue 是此 worker
            $workerJob = (new CallWorker($queueName.$count))
            ->onConnection('database')
            ->onQueue('worker');
    
            $this->dispatch($workerJob);
        }

        // 記錄總共有幾筆
        $redisJobs->set($queueName,$count);

        return ;
    }

    /**
     * 檢查原本在 Redis 有沒有記錄，有的話就刪掉。之後會塞新的進去。
     *
     * @param [String] $queueName
     * @param [type] $redisJobs
     * @return void
     */
    private function checkProcessRecord(String $queueName,$redisJobs)
    {
        $redisRecord = Redis::connection('record');

        if($redisRecord->exists($queueName))
        {
            $redisRecord->del($queueName);
            $redisJobs->del($queueName);
        }

    }

    public function storeDomain($domain, $user, $operationLogInfo=null)
    {
        $domain_id = null;
        $errorMessage = $errorCode = null;

        $domain["name"] = $this->checkDomainFormate($domain['name']);

        try {
            $domainValidate = $this->validateDomain($domain["name"]);
            // 判斷 domain 有沒有各式錯誤
            if(!$domainValidate){
                $errorCode = InputError::DOMAIN_FORMATE_IS_INVALID;
                throw new Exception();
            }

            // domain.cname 為 domain.name 去 . 後再補尾綴 `.user_group_id`
            $domain['cname'] = $this->formatDomainCname($domain["name"], $user["user_group_id"]);

            // 新增 domain
            $domainObj = $this->domainRepository->store($domain, $user, $operationLogInfo);
            if(is_null($domainObj))
                throw $domainObj;
            // 新增 domain 成功
            $domain_id = $domainObj->id;
        } catch (\Exception $e) {
            // 新增 domain 失敗，檢查 (unique) domains.name 已存在 ?
            $result = $this->domainRepository->getDomainIdIfExist($domain["name"], $user["user_group_id"]);

            // domain 早已存在
            if (! is_null($result)) {
                $domain_id = $result->id;
                // 判斷 domain 有沒有 Group
                $result->domainGroup->isEmpty() ? 
                $errorCode = InputError::DOMAIN_ALREADY_EXISTED : $errorMessage = InputError::DOMAIN_ALREADY_HAS_GROUP;
            }

            if (!$errorCode){ //還不知道會有什麼
                $errorCode = InputError::BATCH_DOMAIN_ERROR;
                $errorMessage = $e->getMessage();
            }

        }
        return [$domain, $domain_id, $errorMessage, $errorCode];
    }

    public function storeCdn($domain, $domain_id, $cdn, $user, $isFirstCdn)
    {
        $errorMessage = null;

        $cdn["cname"] = $this->checkDomainFormate($cdn['cname']);

        try {
            // 判斷 cdn.cname 格式(和 domain 規則一樣)是否錯誤，有就不做任何事。
            $cdnCnameValidate = $this->validateDomain($cdn["cname"]);
            if(!$cdnCnameValidate){
                throw new Exception(InputError::getDescription(InputError::CNAME_FORMATE_IS_INVALID));
            }

            // 若非為第一次新增 cdn 之欄位預設值
            $cdn["default"] = 0;
            $cdn["provider_record_id"] = 0;
            
            // 若為第一次新增 cdn 時打 POD
            if ($isFirstCdn) {
                list($cdn, $errorMessage) = $this->createPodRecord($domain, $cdn);
                if (! is_null($errorMessage)) {
                    throw new Exception(InternalError::getDescription(InternalError::DNSPOD_INSERT_ERROR));
                    // throw new \Exception($errorMessage); 原本 DNSPod 回傳的 error
                }
                $isFirstCdn = false;
            }
            
            // 新增 cdn
            $cdn_id = $this->cdnRepository->store($cdn, $domain_id, $user);
            if (! is_int($cdn_id)) {
                throw $cdn_id;
            }
            
        } catch (\Exception $e) {
            $errorMessage = $cdn["cname"]." ".$e->getMessage();
        }
        return [$isFirstCdn, $errorMessage];
    }


    public function createPodRecord($domain, $cdn)
    {
        $errorMessage = null;

        try {
            $dnsPodResponse = $this->dnsProviderService->createRecord(
                [
                    'sub_domain' => $domain['cname'],
                    'value'      => $cdn["cname"],
                    'ttl'        => $cdn["ttl"],
                    'status'     => $cdn["status"]
                ]);

            $success = $this->dnsProviderService->checkAPIOutput($dnsPodResponse);
            if (! $success) {
                throw new \Exception($dnsPodResponse['message']." for ".$cdn["cname"], $dnsPodResponse['errorCode']);
            }
            
            // 成功打 POD 後，改寫 cdn 欄位值
            $cdn["default"] = 1;
            $cdn["provider_record_id"] = $dnsPodResponse['data']['record']['id'];
        } catch (\Exception $e) {
            $errorMessage = $cdn["cname"]." ".$e->getMessage();
        }

        return [$cdn, $errorMessage];
    }

    /**
     * 修改 Domain 格式
     * 
     * 如果最後有 . 會將它刪除
     * 
     * example:
     * sample.com. => sample.com
     *
     * @param string $domain
     * @return string
     */
    private function checkDomainFormate(string $domain): string
    {
        if(substr($domain, strlen($domain)-1,1) == '.'){
            $domain = (substr($domain, 0, -1));
        }

        return strtolower($domain);
    }
}