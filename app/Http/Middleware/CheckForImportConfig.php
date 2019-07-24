<?php

namespace App\Http\Middleware;

use Closure;
use Cache;
use Hiero7\Enums\PermissionError;
use Tymon\JWTAuth\Facades\JWTAuth;


class CheckForImportConfig
{
    /**
     * Handle an incoming request. 如果 匯入config 有在使用會擋下相同 userGroup 的所有公能，但如果 routeName 有 index 就可以照常使用。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $stopUserGroup = Cache::get('Config_userGroupId');

        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token)->toArray();
        
        if($request->method() == 'GET'){
            return $next($request);
        }

        if (Cache::has('Config_userGroupId') &&  $stopUserGroup == $payload['user_group_id']) {
            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::YOUR_GROUP_IS_IMPORTING_CONFIG),
                'errorCode' => PermissionError::YOUR_GROUP_IS_IMPORTING_CONFIG,
            ], 400);
        }
        
        return $next($request);
    }
}
