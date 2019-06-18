<?php

namespace Hiero7\Services;
use Hiero7\Repositories\{CdnRepository, DomainRepository, CdnProviderRepository};
use Hiero7\Services\DnsProviderService;
use DB;

class BatchService{

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

    public function store($domains, $user){
        $errors = [];
        // 批次新增 domain 迴圈
        foreach ($domains as $domain) {
            $error = [];
            $isFirstCdn = true; // // 若為第一次新增 cdn 時打 POD
            try {
                // 新增 domain
                $domainObj = $this->domainRepository->store($domain, $user);
                if(is_null($domainObj))
                    throw $domainObj;
                // 新增 domain 成功
                $domain_user_group_id = $domainObj->user_group_id;
                $domain_id = $domainObj->id;
            } catch (\Exception $e) {
                // 新增 domain 失敗，檢查 (unique) domains.name 已存在 ?
                $result = $this->domainRepository->getDomainIdIfExist($domain["name"], $user["user_group_id"]);

                // domain 不存在且本次新增失敗
                if (! $result->exists()) {
                    $errors[$domain["name"]] = [$e->getMessage()];
                    continue;
                }

                // domain 早已存在
                $domain_user_group_id = $result->user_group_id;
                $domain_id = $result->id;
            }

            // 此 domain 無需新增 cdn
            if (! isset($domain["cdns"]) || empty($domain["cdns"])) {
                continue;
            }

            // 取此權限全部 cdn_providers
            $myCdnProviders = $this->cdnProviderRepository->getCdnProvider($user["user_group_id"]);

            // 批次新增 cdn 迴圈
            foreach ($domain["cdns"] as $key => $cdn) {

                // 此次 $cdn['name'] 換 cdn_providers.id、ttl欄位
                foreach ($myCdnProviders as $mcp) {
                    if ($mcp->name == $cdn["name"]) {
                        $cdn["cdn_provider_id"] = $mcp->id;
                        $cdn["ttl"] = $mcp->ttl;
                        break;
                    }
                }

                // 若此 $cdn['name'] 不匹配 cdn_providers.name
                if(! isset($cdn["cdn_provider_id"])) {
                    array_push($error, 'cdn_providers.name ' . $cdn["name"] . ' doesn\'t exist');
                    continue;
                }

                try {
                    // 若非為第一次新增 cdn 之欄位預設值
                    $cdn["default"] = 0;
                    $cdn["provider_record_id"] = 0;
                    
                    // 若為第一次新增 cdn 時打 POD
                    if ($isFirstCdn) {
                        $isFirstCdn = false;
                        $dnsPodResponse = $this->dnsProviderService->createRecord(
                            [
                                'sub_domain' => $domain["name"].'.'.$domain_user_group_id,
                                'value'      => $cdn["cname"],
                                'ttl'        => $cdn["ttl"],
                                'status'     => true
                            ]);
                        if (! is_null($dnsPodResponse['errorCode']) || array_key_exists('errors', $dnsPodResponse))
                            throw new \Exception($dnsPodResponse['message']." for ".$cdn["cname"], $dnsPodResponse['errorCode']);
                        // 成功打 POD 後，改寫 cdn 欄位值
                        $cdn["default"] = 1;
                        $cdn["provider_record_id"] = $dnsPodResponse['data']['record']['id'];
                    }
                    
                    // 新增 cdn
                    $cdnId = $this->cdnRepository->store($cdn, $domain_id, $user);

                    if (! is_int($cdnId))
                        throw $cdnId;
                    
                } catch (\Exception $e) {
                    $error[] = $cdn["cname"]." ".$e->getMessage();
                }
            }
            $errors[$domain["name"]]=$error;
        }
        return $errors;
    }
}