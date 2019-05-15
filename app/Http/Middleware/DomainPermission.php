<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;
use Tymon\JWTAuth\Facades\JWTAuth;

class DomainPermission
{
    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    /**
     * 驗證 Domain 基本權限
     * 
     * input URI {domain}
     * 
     * 參考
     * Route::delete('{domain}', 'DomainController@destroy');
     * 
     * @param $domain domains_id
     * 
     * 驗證 login Token 是否為最高管理人員 (user_group_id = 1)
     * 是，通過
     * 驗證，domain->user_group_id == login->user_group_id
     * 是，通過
     * Domain Data 不存在時會直接通過
     */
    public function handle($request, Closure $next)
    {
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();

        if (gettype($request->domain) === 'object') {
            $domain = $request->domain;
        } else {
            $domain = $this->domainService->getDomainbyId((int) $request->domain);
        }

        if (($payload['user_group_id'] == 1) || empty($domain) || ($payload['user_group_id'] == $domain->user_group_id)) {
            return $next($request);
        }

        return response()->json([
            'message' => PermissionError::getDescription(PermissionError::YOU_DONT_HAVE_PERMISSION),
            'errorCode' => PermissionError::YOU_DONT_HAVE_PERMISSION,
        ], 400);
    }
}
