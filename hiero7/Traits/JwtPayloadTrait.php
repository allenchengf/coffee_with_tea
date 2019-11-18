<?php

namespace Hiero7\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait JwtPayloadTrait
{
    /**
     * Get JWT Token
     *
     * @return string
     */
    private function getJWTToken(): string
    {
        return JWTAuth::getToken();
    }

    /**
     * Get JWT Payload
     *
     * @return array
     */
    public function getJWTPayload(): array
    {
        return JWTAuth::getPayload($this->getJWTToken())->toArray();
    }

    /**
     * Get Login uid
     */
    public function getJWTUserId()
    {
        return $this->getJWTPayload()['sub'] ?? null;
    }

    /**
     * Get Login User Group Id
     */
    public function getJWTUserGroupId()
    {
        return $this->getJWTPayload()['user_group_id'] ?? null;
    }

    /**
     * Get Login UUID
     */
    public function getJWTUuid()
    {
        return $this->getJWTPayload()['uuid'] ?? null;
    }
}
