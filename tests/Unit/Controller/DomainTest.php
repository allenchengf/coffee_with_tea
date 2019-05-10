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
            'name' => 'leo.test',
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
            'name' => 'rd.test1',
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
            'name' => 'rd.test99',
            'cname' => 'rd.test1',
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
            'name' => 'rd.test99',
            'cname' => 'rd.test99',
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
            'name' => 'rd.test2',
            'cname' => 'rd.test99',
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
            'name' => 'rd.test99',
            'cname' => 'rd.test2',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomian($request, $domain);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    private function addUuidforPayload()
    {
        $this->jwtPayload['uuid'] = \Illuminate\Support\Str::uuid();
        return $this;
    }

    private function addUserGroupId(int $id = 1)
    {
        $this->jwtPayload['user_group_id'] = $id;
        return $this;
    }
}
