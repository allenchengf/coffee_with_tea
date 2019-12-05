<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RolePermissionMappingRequest;
use Hiero7\Repositories\{RolePermissionMappingRepository, PermissionRepository};
use Hiero7\Models\RolePermissionMapping;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Enums\InputError;

class RolePermissionMappingController extends Controller
{
    use JwtPayloadTrait;

    public function __construct(
        RolePermissionMappingRepository $rolePermissionMappingRepository,
        PermissionRepository $permissionRepository
    )
    {
        $this->rolePermissionMappingRepository = $rolePermissionMappingRepository;
        $this->permissionRepository = $permissionRepository;
        // $this->setCategory(config('logging.category.cdn_provider'));
    }

    public function indexByRoleId()
    {
        $rolePermissionMappings = $this->getByRoleId();
        return $this->response("Success", null, $rolePermissionMappings);
    }
    
    public function upsert(RolePermissionMappingRequest $request)
    {
        $jwtPayload = $this->getJWTPayload();
        $edited_by = $jwtPayload['uuid'];
        $role_id = 1;//$jwtPayload['role_id'];

        // table 撈
        $permissions = $this->permissionRepository->index();
        $rolePermissionMappings = $this->rolePermissionMappingRepository->indexByRoleId($role_id);

        // 前端給
        $inputPermissions = collect($request->only('permissions')['permissions']);

        // 前端給定 permissions 其 id 不存在
        foreach ($inputPermissions as $item) {
            $isContains = $permissions->contains($item['permission_id']);
            if (! $isContains) 
                return $this->setStatusCode(400)->response('', InputError::INPUT_PERMISSIONS_NOT_MATCH, ['permission_id' => $item['permission_id']]);
        }

        // 迴圈: 看新增或修改
        $inputPermissions->each(function ($item) use (&$edited_by, &$role_id, &$permissions, &$rolePermissionMappings) {
            $item['role_id'] = $role_id;
            $item['edited_by'] = $edited_by;

            $isExists = $rolePermissionMappings->first(function ($v) use (&$item) {
                return ($v->role_id == $item['role_id']) && ($v->permission_id == $item['permission_id']);
            });
            ! $isExists ? 
            $this->rolePermissionMappingRepository->create($item) : // 新增
            $this->rolePermissionMappingRepository->update($item, $isExists->id); // 修改
        });

        $data = $this->getByRoleId();

        return $this->response('', null, $data);
    }

    public function destroy()
    {
        $role_id = 1;//$jwtPayload['role_id'];
        $this->rolePermissionMappingRepository->delete($role_id);
        return $this->response("Success", null, []);
    }

    public function getByRoleId()
    {
        $role_id = 1;//$this->getJWTPayload()['role_id'];
        $rolePermissionMappings = $this->rolePermissionMappingRepository->indexByRoleId($role_id);
        $permissions = $this->permissionRepository->index();

        $rolePermissionMappings->each(function ($rpm, $iRpm) use (&$permissions, &$rolePermissionMappings) {
            $permissions->each(function ($p, $iP) use (&$rpm, &$iRpm, &$rolePermissionMappings) {
                if ($rolePermissionMappings[$iRpm]['permission_id'] == $p['id'] ) {
                    $rolePermissionMappings[$iRpm]['permission'] = $p;
                    return false;
                }
            });
        });
        return $rolePermissionMappings;
    }
}
