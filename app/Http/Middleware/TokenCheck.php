<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenCheck
{
    public function handle($request, Closure $next)
    {

        try {
            $token = JWTAuth::getToken();
            JWTAuth::getPayload($token)->toArray();
            return $next($request);
        } catch (TokenExpiredException $e) {

            $errorCode = PermissionError::TOKEN_EXPIRED;
        } catch (TokenInvalidException $e) {

            $errorCode = PermissionError::TOKEN_INVALID;
        } catch (JWTException $e) {

            $errorCode = PermissionError::TOKEN_ERROR;
        }

        return response()->json([
            'message' => PermissionError::getDescription($errorCode),
            'errorCode' => $errorCode,
        ])->setStatusCode(401);
    }
}
