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
