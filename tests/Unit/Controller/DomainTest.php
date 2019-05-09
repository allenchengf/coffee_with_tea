<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\DomainController;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Services\DomainService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use DatabaseMigrations;
    protected $domainService;
    protected $jwtPayload = [];

    protected function setUp()
    {
        parent::setUp();
        $this->seed();
        app()->call([$this, 'service']);
        $this->controller = new DomainController($this->domainService);

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
        $request = new Request;

        $request->merge([
            'user_group_id' => 3,
            'name' => 'leo.test',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request);
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
        $errorCode = 4020;
        $request = new Request;

        $request->merge([
            'user_group_id' => 3,
            'name' => 'rd.test1',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request);
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
        $errorCode = 4021;
        $request = new Request;

        $request->merge([
            'user_group_id' => 3,
            'name' => 'rd.test99',
            'cname' => 'rd.test1',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($errorCode, $data['errorCode']);
    }

    private function addUuidforPayload()
    {
        $this->jwtPayload['uuid'] = \Illuminate\Support\Str::uuid();
        return $this;
    }
}
