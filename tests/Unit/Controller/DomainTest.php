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
        $this->seed('DomainTableSeeder');
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
        $target_user_group_id = 2;
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

        // 換頁
        $this->pagination($data);
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
        $target_domain_id = 4;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->getDomainById($this->domain->where('id',$target_domain_id)->first());
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($target_domain_id, $data['data']['domain']['id']);
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
        
        // 換頁
        $this->pagination($data);
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
        
        // 換頁
        $this->pagination($data);
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
        
        // 換頁
        $this->pagination($data);
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
        
        // 換頁
        $this->pagination($data);
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
            'label' => 'LeoLabel123',
        ];

        $request = new Request;
        $request->merge($inputData);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->editDomain($request, $domain);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($inputData['label'], $data['data']['label']);
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

    /**
     * Pagination
     */
    public function pagination($data)
    {
        // 換頁
        $this->assertArrayHasKey('current_page', $data['data']);
        $this->assertArrayHasKey('last_page', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
    }
}
