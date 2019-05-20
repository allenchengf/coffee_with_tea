<?php

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }

    public function testEnvSetting()
    {
        $this->assertTrue(!empty(env('DB_DATABASE')), 'DB_DATABASE is not set');
        $this->assertTrue(!empty(env('JWT_SECRET')), 'JWT_SECRET is not set');
        $this->assertTrue(!empty(env('KONG_OPERATION_LOG')), 'KONG_OPERATION_LOG is not set');
        $this->assertTrue(!empty(env('USER_MODULE')), 'USER_MODULE is not set');
        $this->assertTrue(!empty(env('DNS_PROVIDER_API')), 'DNS_PROVIDER_API is not set');
        $this->assertTrue(!empty(env('DNS_POD_DOMAIN')), 'DNS_POD_DOMAIN is not set');
        $this->assertTrue(!empty(env('DNS_POD_LOGIN_TOKEN')), 'DNS_POD_LOGIN_TOKEN is not set');
        $this->assertTrue(!empty(env('DNS_POD_DOMAIN_ID')), 'DNS_POD_DOMAIN_ID is not set');
        $this->assertTrue(!empty(env('DOMAIN_REGULAR')), 'DOMAIN_REGULAR is not set');
        $this->assertTrue(!empty(env('CDN_TTL')), 'CDN_TTL is not set');
    }
}
