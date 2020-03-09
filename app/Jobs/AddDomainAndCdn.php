<?php

namespace App\Jobs;

use Hiero7\Enums\InputError;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class AddDomainAndCdn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 1;
    public $timeout = 600;
    public $domain, $user, $batchService, $queueName, $redis, $operationLogInfo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $domain, array $user, string $queueName, array $operationLogInfo)
    {
        $this->domain           = $domain;
        $this->user             = $user;
        $this->queueName        = $queueName;
        $this->redis            = Redis::connection('record');
        $this->operationLogInfo = $operationLogInfo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $domainError = $cdnSuccess = $cdnError = $success = $failure = [];
        // 新增或查詢已存在 domain
        list($domainResult, $domain_id, $errorMessage, $errorCode) = app('Hiero7\Services\BatchService')->storeDomain($this->domain, $this->user, $this->operationLogInfo);

        if (!is_null($errorCode) || !is_null($errorMessage)) {
            $domainError = [
                'name'      => $domainResult["name"],
                'errorCode' => $errorCode,
                'message'   => InputError::getDescription($errorCode),
                // 目前不接 domain 沒有給 cdn 的 error 所以先註解起來摟～
                // 'errorCode' => !is_null($errorCode)? $errorCode:111,
                // 'message' => !is_null($errorCode)?InputError::getDescription($errorCode):'This domain has been stored with no cdns.',
                'cdn'       => [],
            ];

        }

        if (!empty($domainError)) {
            //有 Domain error
            //針對已經存在的domain 還是要處理之後的 cdn
            if ($errorCode == 4046 && isset($domainResult['cdns'])) {
                list($cdnSuccess, $cdnError) = app('Hiero7\Services\BatchService')->handelCdn($this->user, $domain_id, $domainResult, $this->operationLogInfo);

                if (!empty($cdnSuccess)) {
                    $success[] = ['name' => $domainResult['name'], 'cdn' => $cdnSuccess];
                }
            }

            $domainError['cdn'] = $cdnError;
            $failure[]          = $domainError;

        } else {
            //無 Domain error
            //處理之後的 cdn
            if (isset($domainResult['cdns'])) {
                list($cdnSuccess, $cdnError) = app('Hiero7\Services\BatchService')->handelCdn($this->user, $domain_id, $domainResult, $this->operationLogInfo);

                if (!empty($cdnError)) {
                    //有 Cdn error
                    $failure[] = [
                        'name'      => $domainResult['name'],
                        'errorCode' => null,
                        'message'   => null,
                        'cdn'       => $cdnError,
                    ];
                }
            }
            //無 Cdn error
            $success[] = ['name' => $domainResult['name'], 'cdn' => $cdnSuccess];

        }

        $array = [
            'success' => ['domain' => $success],
            'failure' => ['domain' => $failure],
        ];

        //塞入處理結果在 Redis
        $this->redis->lpush($this->queueName, json_encode($array));

        return;
    }

}
