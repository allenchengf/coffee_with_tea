<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;
use Hiero7\Traits\JwtPayloadTrait;

class DomainPermission
{
    use JwtPayloadTrait;

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
     * Domain 不存在時，也會直接通過
     */
    public function handle($request, Closure $next)
    {
        $domain = ($request->domain instanceof Domain) ? $request->domain : $this->domainService->getDomainById((int) $request->domain);

        if (($this->getJWTUserGroupId() == 1) || empty($domain) || ($this->getJWTUserGroupId() == $domain->user_group_id)) {
            return $next($request);
        }

        return response()->json([
            'message' => PermissionError::getDescription(PermissionError::YOU_DONT_HAVE_PERMISSION),
            'errorCode' => PermissionError::YOU_DONT_HAVE_PERMISSION,
        ], 400);
    }
}
