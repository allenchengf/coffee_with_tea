<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    /**
     * 設定 JWT Token Payload
     * 
     * 可自由加入 Payload
     * 
     * @param integer $uid
     * @param array $data
     * @return array
     */
    public function setJwtTokenPayload($uid = 1, $data = ['platformKey' => 'u9fiaplome']) : array
    {
        JWTFactory::sub($uid);
        foreach ($data as $key => $value) {
            JWTFactory::$key($value);
        }

        $payload = JWTFactory::make();

        $token = JWTAuth::encode($payload);
        JWTAuth::setToken($token);
        return JWTAuth::getPayload($token)->toArray();
    }
}
