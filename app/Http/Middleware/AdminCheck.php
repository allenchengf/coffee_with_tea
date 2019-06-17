<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Hiero7\Services\UserModuleService;

class AdminCheck
{
    protected $userModuleService;

    public function __construct(UserModuleService $userModuleService)
    {
        $this->userModuleService = $userModuleService;
    }

    /**
     * 檢查 Login User 是否為 user_group = 1 和 其餘 user_group 但 level = 1 的 Admin  
     * 
     * 需要 JWT Token
     */
    public function handle($request, Closure $next)
    {
        $response = $this->userModuleService->getSelf($request);
        if (empty($response['data']) || $response['data']['user_group_id'] != 1 && $response['data']['level'] != 1) {
            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::YOU_DONT_HAVE_PERMISSION),
                'errorCode' => PermissionError::YOU_DONT_HAVE_PERMISSION,
            ], 400);
        }

        return $next($request);
    }
}
