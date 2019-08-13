<?php

namespace Hiero7\Traits;

use Tymon\JWTAuth\Facades\JWTAuth;

trait JwtPayloadTrait
{
    public function getJWTPayload()
    {
        $token = JWTAuth::getToken();
        return JWTAuth::getPayload($token)->toArray();
    }

    public function getJWTUserGroupId()
    {
        return $this->getJWTPayload()['user_group_id'];
    }

    public function getJWTUuid()
    {
        return $this->getJWTPayload()['uuid'];
    }
}