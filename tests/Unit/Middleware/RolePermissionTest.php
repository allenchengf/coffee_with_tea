<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Middleware\RolePermission;
use Hiero7\Models\{Api, Permission, ApiPermission, RolePermissionMapping};

class RolePermissionTest extends TestCase
{
    use DatabaseMigrations;
    protected $pathPrefix = '/api/v1/';
    protected $middleware;
    protected $jwtPayload = [];
    protected $apis;

    protected function setUp()
    {
        parent::setUp();
        
        $this->seed('ApisTableSeeder');
        $this->seed('PermissionSeeder');
        $this->seed('ApiPermissionMappingTableSeeder');
        $this->seed('RolePermissionMappingSeeder');

        $this->middleware = new RolePermission;

        // 準備共用測試資料
        $this->apis = (new Api)->all();
        foreach ($this->apis as $i => $row) {
            $path = str_replace('\\', '', $row['path_regex']);
            $path = str_replace('[0-9]+', '1', $path);
            $this->apis[$i]['path_regex'] = str_replace('[a-zA-Z]+', 'Domain', $path);
        }
    }

    /**
     * @test
     */
    public function successByGod()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $role_id = 999;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row['path_regex']]);
            $request->setMethod($row['method']);

            $response = $this->middleware->handle($request, function () {});
            $this->assertEquals($response, null);
        }
    }

    /**
     * @test
     */
    public function successByRoleIdExists()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $role_id = 1;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row['path_regex']]);
            $request->setMethod($row['method']);

            $response = $this->middleware->handle($request, function () {});
            $this->assertEquals($response, null);
        }
    }

    /**
     * @test
     */
    public function failByRoleIdNotExists()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $role_id = 999;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row['path_regex']]);
            $request->setMethod($row['method']);

            $response = $this->middleware->handle($request, function () {});

            $data = json_decode($response->getContent(), true);

            $this->assertEquals($response->status(), 400);
            $this->assertEquals($data['message'], "Role Permission Denied.");
        }
    }
}
