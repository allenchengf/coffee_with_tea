<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\DomainController;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DomainTest extends TestCase
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
        $this->controller = new DomainController($this->domainService);
        $this->domain = new Domain();

    }

    public function service(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    /**
     * Get Domain
     * by Admin
     * @test
     */
    public function getDomain()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 3;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($target_user_group_id, $data['data']['domains'][0]['user_group_id']);
    }

    /**
     * Get Domain
     * by user
     * by domian_id
     * @test
     */
    public function getDomain_by_domain_id()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $target_domain_id = 3;
        $request = new Request;

        $request->merge([
            'domain_id' => $target_domain_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($target_domain_id, $data['data']['domains'][0]['id']);
    }

    /**
     * Get Domain
     * login by user
     * @test
     */
    public function getDomain_login_by_user()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $target_user_group_id = 2;
        $request = new Request;

        $request->merge(compact('user_group_id'));

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user_group_id, $data['data']['domains'][0]['user_group_id']);
    }

    /**
     * Get Domain
     * login by user
     * target other user_group_id
     * @test
     */
    public function getDomain_login_by_user_other_user_group_id()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $target_user_group_id = 3;
        $request = new Request;

        $request->merge(compact('user_group_id'));

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user_group_id, $data['data']['domains'][0]['user_group_id']);
    }

    /**
     * Get Domain
     * by Admin
     * @test
     */
    public function getDomain_all()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $all = true;
        $expectedCount = $this->domain->count();
        $request = new Request;

        $request->merge([
            'all' => $all,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertCount($expectedCount, $data['data']['domains']);
    }

    /**
     * Get Domain
     * by User
     * @test
     */
    public function getDomain_all_but_not_admin()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $all = true;
        $expectedCount = $this->domain->where(compact('user_group_id'))->count();
        $request = new Request;

        $request->merge([
            'all' => $all,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomain($request, $this->domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertCount($expectedCount, $data['data']['domains']);
    }

    /**
     * Create Domain
     *
     * @test
     */
    public function createDomain()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $request = new Request;

        $request->merge([
            'name' => 'leo.test3.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request, $this->domain);
        $this->assertEquals(200, $response->status());
    }

    /**
     * Create Exist Domain
     *
     * @test
     */
    public function create_Exist_Domain()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $errorCode = 4020;
        $request = new Request;

        $request->merge([
            'user_group_id' => 3,
            'name' => 'rd.test1.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request, $this->domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Create Exist CNAME
     *
     * @test
     */
    public function create_Exist_CNAME()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $errorCode = 4021;
        $request = new Request;

        $request->merge([
            'user_group_id' => $user_group_id,
            'name' => 'rd.test99.com',
            'cname' => 'rd.test1.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request, $this->domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Create domain error
     *
     * @test
     */
    public function create_NAME_Error()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $errorCode = 4024;
        $request = new Request;

        $request->merge([
            'user_group_id' => $user_group_id,
            'name' => 'rd.test99',
            'cname' => 'rd.test1.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request, $this->domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Edit Domain
     *
     * @test
     */
    public function edit_Domian()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 3;
        $domain = $this->domain->find($domain_id);

        $inputData = [
            'domain' => $domain_id,
            'name' => 'rd.test99.com',
            'cname' => 'rd.test99.com',
        ];

        $request = new Request;
        $request->merge($inputData);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomian($request, $domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($inputData['name'], $data['data']['name']);
        $this->assertEquals($inputData['cname'], $data['data']['cname']);
    }

    /**
     * Edit Exist Domain
     *
     * @test
     */
    public function edit_Domian_Exist_Domain()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $errorCode = 4020;
        $domain_id = 3;
        $request = new Request;
        $domain = $this->domain->find($domain_id);

        $request->merge([
            'domain' => $domain_id,
            'name' => 'rd.test2.com',
            'cname' => 'rd.test99.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomian($request, $domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Edit Exist Cname
     *
     * @test
     */
    public function edit_Domian_Exist_Cname()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $errorCode = 4021;
        $domain_id = 3;
        $request = new Request;
        $domain = $this->domain->find($domain_id);

        $request->merge([
            'name' => 'rd.test99.com',
            'cname' => 'rd.test2.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomian($request, $domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Edit domain Error
     *
     * @test
     */
    public function edit_Domian_Error()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $errorCode = 4024;
        $domain_id = 3;
        $request = new Request;
        $domain = $this->domain->find($domain_id);

        $request->merge([
            'name' => 'rd.test99',
            'cname' => 'rd.test2.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomian($request, $domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /**
     * Delete Domain
     *
     * @test
     */
    public function delete_domain()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 3;
        $request = new Request;
        $domain = $this->domain->find($domain_id);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->destroy($domain);
        $this->assertEquals(200, $response->status());
    }
}
