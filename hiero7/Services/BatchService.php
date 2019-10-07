<?php

namespace Hiero7\Services;
use Hiero7\Repositories\{CdnRepository, DomainRepository, CdnProviderRepository};
use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\DomainHelperTrait;
use Illuminate\Support\Collection;
use DB;


class BatchService{

    use DomainHelperTrait;

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

    public function store($domains, $user)
    {
        $errors = [];
        $success = $failure = [];
        // 取此權限全部 cdn_providers
        $myCdnProviders = collect($this->cdnProviderRepository->getCdnProvider($user["user_group_id"])->toArray());

        // 批次新增 domain 迴圈
        foreach ($domains as $domain) {
            // $error = [];
            $domainError = [];
            // 新增或查詢已存在 domain
            list($domain, $domain_id, $errorMessage) = $this->storeDomain($domain, $user);

            if (! is_null($errorMessage) || ! isset($domain["cdns"]) || empty($domain["cdns"])) {
                
                $domainError = [
                    'name' => $domain["name"],
                    'errorCode' => ! is_null($errorMessage)?110:111,
                    'message' => !is_null($errorMessage)?$errorMessage:'This domain has been stored with no cdns.',
                    'cdn' => []
                ];
                
                $failure[] = $domainError;

                continue;
            }


            // 查詢 cdns.domain_id 是否存在 ? 不存在才打 POD，代表 POD 的 default 尚未存在
            $cdns = $this->cdnRepository->indexByWhere(['domain_id' => $domain_id]);
            $isFirstCdn = count($cdns) == 0 ? true : false;

            $cdnSuccess = $cdnError = [];
            // 批次新增 cdn 迴圈
            foreach ($domain["cdns"] as $cdn) {
                // $cdn["name"] = trim($cdn["name"]);
                $cdn["cname"] = strtolower($cdn["cname"]);

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

            $success[] = ['name' => $domain['name'], 'cdn' => $cdnSuccess];
            //接 Domain 通過但 cdn 可能會有錯誤的。因為 domain 有錯誤上面就 continue 掉了歐！
            if(!empty($cdnError)){
                $failure[] = ['name' => $domain['name'], 'cdn' => $cdnError];
            }

        }


        $result = ['success' => ['domain' => $success],
                    'failure' => ['domain' =>  $failure],
                    ];

        return $result;
    }

    public function storeDomain($domain, $user)
    {
        $domain_id = null;
        $errorMessage = null;
        $domain["name"] = strtolower($domain["name"]);

        try {
            // domain.cname 為 domain.name 去 . 後再補尾綴 `.user_group_id`
            $domain['cname'] = $this->formatDomainCname($domain["name"], $user["user_group_id"]);
            // 新增 domain
            $domainObj = $this->domainRepository->store($domain, $user);
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
            } else {
                $errorMessage = $e->getMessage();
            }

        }
        return [$domain, $domain_id, $errorMessage];
    }


    public function storeCdn($domain, $domain_id, $cdn, $user, $isFirstCdn)
    {
        $errorMessage = null;

        try {
            // 若非為第一次新增 cdn 之欄位預設值
            $cdn["default"] = 0;
            $cdn["provider_record_id"] = 0;
            
            // 若為第一次新增 cdn 時打 POD
            if ($isFirstCdn) {
                list($cdn, $errorMessage) = $this->createPodRecord($domain, $cdn);
                if (! is_null($errorMessage)) {
                    throw new \Exception($errorMessage);
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
}