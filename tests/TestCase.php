<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionClass;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected $jwtPayload = [];
    protected $response;
    protected $responseArrayData = [];

    /**
     * 設定 JWT Token Payload
     *
     * 可自由加入 Payload
     *
     * @param integer $uid
     * @param array $data
     * @return array
     */
    public function setJwtTokenPayload($uid = 1, $data = ['platformKey' => 'u9fiaplome']): array
    {
        JWTFactory::sub($uid);
        
        $data = array_merge($this->jwtPayload, $data);

        foreach ($data as $key => $value) {
            JWTFactory::$key($value);
        }

        $payload = JWTFactory::make();

        $token = JWTAuth::encode($payload);
        JWTAuth::setToken($token);
        return JWTAuth::getPayload($token)->toArray();
    }

    public function addUuidforPayload()
    {
        $this->jwtPayload['uuid'] = \Illuminate\Support\Str::uuid();
        return $this;
    }

    public function addUserGroupId(int $id = 1)
    {
        $this->jwtPayload['user_group_id'] = $id;
        return $this;
    }

    public function addRoleIdforPayload(int $id = 1)
    {
        $this->jwtPayload['role_id'] = $id;
        return $this;
    }

    protected function initMock($class)
    {
        $mock = \Mockery::mock($class);
        $this->app->instance($class, $mock);
        return $mock;
    }

    /**
     * getPrivateMethod
     *
     * @author    Joe Sexton <joe@webtipblog.com>
     * @param     string $className
     * @param     string $methodName
     * @return    ReflectionMethod
     */
    public function getPrivateMethod($className, $methodName)
    {
        $reflector = new ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    protected function checkoutResponse(int $code = 200)
    {
        if ($this->response) {
            $this->assertEquals($code, $this->response->status());
            $this->responseArrayData = json_decode($this->response->getContent(), true);
        }
    }
}
