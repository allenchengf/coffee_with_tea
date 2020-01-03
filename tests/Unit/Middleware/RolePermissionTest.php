<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Middleware\RolePermission;
use DB;

class RolePermissionTest extends TestCase
{
    use DatabaseMigrations;
    protected $pathPrefix = '/api/v1/';
    protected $middleware;
    protected $fakeMiddleware;
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
        $this->fakeMiddleware = new FakeRolePermission;

        // 準備共用測試資料
        $this->apis = DB::table('apis as a')
                        ->leftjoin('api_permission_mapping as apm', 'a.id', '=', 'apm.api_id')
                        ->leftjoin('permissions as p', 'apm.permission_id', '=', 'p.id')
                        ->select('a.method', 'a.path_regex', 'p.id as permission_id')
                        ->get();

        foreach ($this->apis as $i => $row) {
            $path = str_replace('\\', '', $row->path_regex);
            $path = str_replace('[0-9]+', '1', $path);
            $this->apis[$i]->path_regex = str_replace('[a-zA-Z]+', 'Domain', $path);
        }
    }

    /**
     * @test
     */
    public function successByGod()
    {
        $loginUid = 1;
        $user_group_id = 1; // God 上帝視角
        $role_id = 999; // 上帝無視 role

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);

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
        $user_group_id = 2; // 一般人
        $role_id = 1; // 存在的 role

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);
            $request->headers->set('permission_id', $row->permission_id);

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
        $user_group_id = 2; // 一般人
        $role_id = 999; // 不存在的 role

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);
            $request->headers->set('permission_id', $row->permission_id);

            $response = $this->middleware->handle($request, function () {});

            $data = json_decode($response->getContent(), true);

            $this->assertEquals($response->status(), 400);
            $this->assertEquals($data['message'], "Role Permission Denied.");
        }
    }

    /**
     * @test
     */
    public function failByNotPassPermissionId()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $role_id = 1;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);

            $response = $this->middleware->handle($request, function () {});

            $data = json_decode($response->getContent(), true);

            $this->assertEquals($response->status(), 400);
            $this->assertEquals($data['message'], "Please Pass Permission ID.");
        }
    }

    /**
     * @test
     */
    public function failByPermissionIdMappingErr()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $role_id = 1;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);
            $request->headers->set('permission_id', $row->permission_id + 999);

            $response = $this->middleware->handle($request, function () {});

            $data = json_decode($response->getContent(), true);

            $this->assertEquals($response->status(), 400);
            $this->assertEquals($data['message'], "Role Permission Denied.");
        }
    }

    /**
     * @test
     */
    public function failByCrudClose()
    {
        $loginUid = 1;
        $user_group_id = 2;
        $role_id = 1;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->addRoleIdforPayload($role_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        foreach ($this->apis as $row) {
            $request = new Request([], [], [], [], [], ['REQUEST_URI' => $this->pathPrefix . $row->path_regex]);
            $request->setMethod($row->method);
            $request->headers->set('permission_id', $row->permission_id);

            $response = $this->fakeMiddleware->handle($request, function () {});

            $data = json_decode($response->getContent(), true);

            $this->assertEquals($response->status(), 400);
            $this->assertEquals($data['message'], "You Don't Have Role Permission.");
        }
    }
}


class FakeRolePermission extends RolePermission
{
    public function getPermissionsByRoleId($role_id, $permission_id)
    {
        return DB::table('permissions')
                    ->where('rpm.role_id', $role_id)
                    ->where('permissions.id', $permission_id)
                    ->leftjoin('role_permission_mapping as rpm', 'permissions.id', '=', 'rpm.permission_id')
                    ->leftjoin('api_permission_mapping as apm', 'permissions.id', '=', 'apm.permission_id')
                    ->leftjoin('apis as a', 'apm.api_id', '=', 'a.id')
                    ->select('a.method', 'a.path_regex', '\'{\"read\":0,\"create\":0,\"update\":0,\"delete\":0}"}\' as actions')
                    ->get();
    }
}