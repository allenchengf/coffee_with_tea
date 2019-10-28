<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Services\DnsProviderService;
use Hiero7\Enums\InternalError;

class CheckDnsPod
{
    protected $userModuleService;

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
    }
    
    /**
     * 先去測試 DnsProvider 的 DnsPod 是否正常，用 getDomain 這只代表
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $this->dnsProviderService->getDomain();

        // 如果有 Dns Provider statusCode 400 就 return 出去 statusCode 503 
        if($response->status == 400){
            return response()->json([
                'message' => InternalError::getDescription(InternalError::DNSPOD_ERROR),
                'errorCode' => InternalError::DNSPOD_ERROR,
            ])->setStatusCode(503);
        }

        return  $next($request);
    }
}
