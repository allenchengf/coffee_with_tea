<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;



class AddDomainAndCdn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $domain, $user, $cdnProviders, $batchService, $queueName ,$redis;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $domain, array $user, Collection $cdnProviders, string $queueName)
    {
        $this->domain = $domain;
        $this->user = $user;
        $this->cdnProviders = $cdnProviders;
        $this->queueName = $queueName;
        $this->redis = Redis::connection('record');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $domainError = $success = $failure = [];
        // 新增或查詢已存在 domain
        list($domainResult, $domain_id, $errorMessage) = app('Hiero7\Services\BatchService')->storeDomain($this->domain, $this->user);

        if (! is_null($errorMessage) || ! isset($domainResult["cdns"]) || empty($domainResult["cdns"])) {
            
            $domainError = [
                'name' => $domainResult["name"],
                'errorCode' => ! is_null($errorMessage)?110:111,
                'message' => !is_null($errorMessage)?$errorMessage:'This domain has been stored with no cdns.',
                'cdn' => []
            ];
            
            $failure[] = $domainError; //記錄失敗
        }

        if(empty($domainError)){

            // 查詢 cdns.domain_id 是否存在 ? 不存在才打 POD，代表 POD 的 default 尚未存在
            $cdns = app('Hiero7\Repositories\CdnRepository')->indexByWhere(['domain_id' => $domain_id]);
            $isFirstCdn = count($cdns) == 0 ? true : false;

            $cdnSuccess = $cdnError = [];
            // 批次新增 cdn 迴圈
            foreach ($domainResult["cdns"] as $cdn) {
                $cdn["cname"] = strtolower($cdn["cname"]);

                // 此次 $cdn['name'] 換 cdn_providers.id、ttl欄位
                $this->cdnProviders->each(function ($v) use (&$cdn) {
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
                list($isFirstCdn, $errorMessage) = app('Hiero7\Services\BatchService')->storeCdn($domainResult, $domain_id, $cdn, $this->user, $isFirstCdn);
                if (! is_null($errorMessage)) {

                    $cdnError[] = ['name' => $cdn["name"],
                                    'errorCode' => 113,
                                    'message' => $errorMessage];
                    
                    continue;
                }

                $cdnSuccess[] = ['name' => $cdn["name"]]; 
            }

            $success[] = ['name' => $domainResult['name'], 'cdn' => $cdnSuccess];
            //接 Domain 通過但 cdn 可能會有錯誤的。若 domain 有錯誤就不會進此回圈
            if(!empty($cdnError)){
                $failure[] = ['name' => $domainResult['name'],
                                'errorCode' => null,
                                'message' => null , 'cdn' => $cdnError];
            }
        }

        // 到時候要接結果留的
        $array = ['success' => ['domain' => $success],
                    'failure' => ['domain' =>  $failure]
                ];

        //塞入處理結果在 Redis
        $this->redis->lpush($this->queueName,json_encode($array));

    }

}
