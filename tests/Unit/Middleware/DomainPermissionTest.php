<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\DomainPermission;
use Illuminate\Http\Request;
use Hiero7\Services\DomainService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Hiero7\Models\Domain;

class DomainPermissionTest extends TestCase
{
    use DatabaseMigrations;
    protected $domainService;
    protected $domain;
    protected $jwtPayload = [];

    protected function setUp()
    {
        parent::setUp();
        $this->seed();
        app()->call([$this, 'service']);
        $this->domain = new Domain();
        $this->middleware = new DomainPermission($this->domainService);
    }

    public function service(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    /**
     * Undocumented function
     *
     * @test
     */
    public function succcess()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 3;
        
        $request = new Request;
        $request->merge([
            'domain' => $domain_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response, null);
    }

    /**
     * Undocumented function
     *
     * @test
     */
    public function succcess_login_admin()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $domain_id = 4;
        
        $request = new Request;
        $request->merge([
            'domain' => $domain_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response, null);
    }

    /**
     * 測試沒有編輯權限
     * 
     * domain is object
     *
     * @test
     */
    public function success_by_domain_object()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 3;
        $domain = $this->domain->find($domain_id);
        $request = new Request;

        $request->merge([
            'domain' => $domain,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response, null);
    }

    /**
     * 測試沒有 Domain
     *
     * @test
     */
    public function noHaveDomain()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 99;
        
        $request = new Request;
        $request->merge([
            'domain' => $domain_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response, null);
    }

    /**
     * 測試沒有編輯權限
     *
     * @test
     */
    public function noHavePermission()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 1;
        
        $request = new Request;
        $request->merge([
            'domain' => $domain_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response->getStatusCode(), 400);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3002, $data['errorCode']);
    }

    /**
     * 測試沒有編輯權限
     * 
     * domain is object
     *
     * @test
     */
    public function noHavePermission_by_domain_object()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 1;
        $domain = $this->domain->find($domain_id);
        $request = new Request;

        $request->merge([
            'domain' => $domain,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->middleware->handle($request, function () {});
        $this->assertEquals($response->getStatusCode(), 400);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3002, $data['errorCode']);
    }
}
