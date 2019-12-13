<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Hiero7\Traits\JwtPayloadTrait;

class InternalGroup
{
    use JwtPayloadTrait;

    /**
     * 檢查 Login User 是否為內部群組
     * user_group_id == 1
     * 通過
     *
     * 需要 JWT Token
     */
    public function handle($request, Closure $next)
    {
        if ($this->getJWTUserGroupId() != 1) {
            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::YOU_DONT_HAVE_PERMISSION),
                'errorCode' => PermissionError::YOU_DONT_HAVE_PERMISSION,
            ], 400);
        }

        return $next($request);
    }
}
