<?php

namespace App\Http\Middleware;

use Closure;
use Hiero7\Enums\PermissionError;
use Hiero7\Traits\JwtPayloadTrait;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class TokenCheck
{
    use JwtPayloadTrait;

    public function handle($request, Closure $next)
    {
        try {

            $request->merge(['edited_by' => $this->getJWTUuid()]);

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
