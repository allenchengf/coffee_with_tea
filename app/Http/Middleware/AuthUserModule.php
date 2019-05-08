<?php

namespace App\Http\Middleware;

use Closure;
use Ixudra\Curl\Facades\Curl;
use Hiero7\Services\UserModuleService;

class AuthUserModule
{
    protected $userModuleService;

    public function __construct(UserModuleService $userModuleService)
    {
        $this->userModuleService = $userModuleService;
    }

    public function handle($request, Closure $next)
    {
        $response = $this->userModuleService->authorization($request);
        return $response['errorCode'] ? response()->json($response) : $next($request);
    }
}
