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

            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::TOKEN_EXPIRED),
                'errorCode' => PermissionError::TOKEN_EXPIRED,
            ])->setStatusCode(403);
        } catch (TokenInvalidException $e) {

            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::TOKEN_INVALID),
                'errorCode' => PermissionError::TOKEN_INVALID,
            ])->setStatusCode(403);
        } catch (JWTException $e) {

            return response()->json([
                'message' => PermissionError::getDescription(PermissionError::TOKEN_ERROR),
                'errorCode' => PermissionError::TOKEN_ERROR,
            ])->setStatusCode(403);
        }
    }
}
