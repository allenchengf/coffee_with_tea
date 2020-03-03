<?php

namespace App\Http\Middleware;

use Cache;
use Closure;
use Hiero7\Enums\InternalError;
use Hiero7\Services\DnsProviderService;

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
        $dnspodCheckKey = "dnspod_status";

        if (Cache::has($dnspodCheckKey)) {
            return $next($request);
        }

        $response = $this->dnsProviderService->getDomain();

        // 如果有 Dns Provider statusCode 400 就 return 出去 statusCode 503
        if ($response->status == 400) {
            Cache::forget($dnspodCheckKey);
            return response()->json([
                'message'   => InternalError::getDescription(InternalError::DNSPOD_ERROR),
                'errorCode' => InternalError::DNSPOD_ERROR,
            ])->setStatusCode(503);
        } else {
            Cache::put($dnspodCheckKey, true, 600);
        }

        return $next($request);
    }
}
