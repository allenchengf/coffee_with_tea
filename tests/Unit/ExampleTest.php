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
        $this->assertTrue(!empty(env('OPERATION_LOG_URL')), 'OPERATION_LOG_URL is not set');
        $this->assertTrue(!empty(env('USER_MODULE')), 'USER_MODULE is not set');
        $this->assertTrue(!empty(env('DNS_PROVIDER_API')), 'DNS_PROVIDER_API is not set');
        $this->assertTrue(!empty(env('DNS_POD_DOMAIN')), 'DNS_POD_DOMAIN is not set');
        $this->assertTrue(!empty(env('DNS_POD_LOGIN_TOKEN')), 'DNS_POD_LOGIN_TOKEN is not set');
        $this->assertTrue(!empty(env('DNS_POD_DOMAIN_ID')), 'DNS_POD_DOMAIN_ID is not set');
        $this->assertTrue(!empty(env('DOMAIN_REGULAR')), 'DOMAIN_REGULAR is not set');
        $this->assertTrue(!empty(env('CDN_TTL')), 'CDN_TTL is not set');
        $this->assertTrue(!empty(env('SCHEME')), 'SCHEME is not set');
        $this->assertTrue(!empty(env('PLATFORM_KEY')), 'PLATFORM_KEY is not set');
        $this->assertTrue(!empty(env('CONFIG_WAIT_TIME')), 'CONFIG_WAIT_TIME is not set');
        $this->assertTrue(!empty(env('AWS_ACCESS_KEY_ID')), 'AWS_ACCESS_KEY_ID is not set');
        $this->assertTrue(!empty(env('AWS_SECRET_ACCESS_KEY')), 'AWS_SECRET_ACCESS_KEY is not set');
        $this->assertTrue(!empty(env('AWS_REGION')), 'AWS_REGION is not set');
        $this->assertTrue(!empty(env('S3_BUCKET_NAME_CONFIG_BACKUP')), 'S3_BUCKET_NAME_CONFIG_BACKUP is not set');
        $this->assertTrue(!empty(env('BACKUP_AT')), 'BACKUP_AT is not set');
    }
}
