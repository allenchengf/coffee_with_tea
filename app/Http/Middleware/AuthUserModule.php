<?php

namespace App\Http\Middleware;

use Closure;
use Ixudra\Curl\Facades\Curl;
use Hiero7\Services\UserModuleService;
use Tymon\JWTAuth\Facades\JWTAuth;

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

        if(is_null($response) && $response['errorCode'])
            return response()->json($response);
            
        $token = JWTAuth::getToken();
        $user = array_only(JWTAuth::getPayload($token)->toArray(), ['uuid','user_group_id']);
        
        $request->attributes->add(['user' => $user]);
        return $next($request);
    }
}
