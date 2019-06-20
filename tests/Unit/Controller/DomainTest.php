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
    protected $domainService, $domain, $jwtPayload = [];

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
     * Get Domain By Id
     * by Admin
     * @test
     */
    public function getDomainById()
    {
        $domain_id = 3;
        $domain = $this->domain->find($domain_id);

        $response = $this->controller->getDomainById($domain);
        $this->assertEquals(200, $response->status());
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
     * by domain_id
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
            'label' => 'LeoLabel',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request, $this->domain);
        $this->assertEquals(200, $response->status());
    }


    /**
     * Edit Domain
     *
     * @test
     */
    public function edit_Domain()
    {
        $loginUid = 4;
        $user_group_id = 2;
        $domain_id = 3;
        $domain = $this->domain->find($domain_id);

        $inputData = [
            'domain' => $domain_id,
            'name' => 'rd.test99.com',
            'cname' => 'rd.test99.com',
            'label' => 'LeoLabel',
        ];

        $request = new Request;
        $request->merge($inputData);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomain($request, $domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($inputData['name'], $data['data']['name']);
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
