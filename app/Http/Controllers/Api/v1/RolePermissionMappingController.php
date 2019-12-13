<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RolePermissionMappingRequest;
use Hiero7\Repositories\{RolePermissionMappingRepository, PermissionRepository};
use Hiero7\Models\RolePermissionMapping;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Enums\{InputError, PermissionError};

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

    public function indexSelf()
    {
        $jwtPayload = $this->getJWTPayload();
        $roleId = 1;//$this->getJWTPayload()['role_id'];
        $rolePermissionMappings = $this->getByRoleId($roleId);
        return $this->response("Success", null, $rolePermissionMappings);
    }

    public function indexByRoleId($roleId)
    {
        $jwtPayload = $this->getJWTPayload();

        // curl User Module: $roleId 其 ugid
        // return 400: ugid 為 null
        $ugid = 2;
        
        // return 400: 非上帝視角 且 ugid 經比對不同者。不可修改別 group 的 role
        if ($jwtPayload['user_group_id'] !== 1 && $jwtPayload['user_group_id'] != $ugid)
            return $this->setStatusCode(400)->response('', PermissionError::PERMISSION_DENIED, []);

        $rolePermissionMappings = $this->getByRoleId($roleId);
        return $this->response("Success", null, $rolePermissionMappings);
    }

    public function upsert(RolePermissionMappingRequest $request, $roleId)
    {
        $jwtPayload = $this->getJWTPayload();
        $edited_by = $jwtPayload['uuid'];

        // curl User Module: $roleId 其 ugid
        // return 400: ugid 為 null
        $ugid = 2;
        
        // return 400: 非上帝視角 且 ugid 經比對不同者。不可修改別 group 的 role
        if ($jwtPayload['user_group_id'] !== 1 && $jwtPayload['user_group_id'] != $ugid)
            return $this->setStatusCode(400)->response('', PermissionError::PERMISSION_DENIED, []);

        // table 撈
        $permissions = $this->permissionRepository->index();
        $rolePermissionMappings = $this->rolePermissionMappingRepository->indexSelf($roleId);

        // 前端給
        $inputPermissions = collect($request->only('permissions')['permissions']);

        // return 400: 前端給定 permissions 其 id 不存在
        foreach ($inputPermissions as $item) {
            $isContains = $permissions->contains($item['permission_id']);
            if (! $isContains)
                return $this->setStatusCode(400)->response('', InputError::INPUT_PERMISSIONS_NOT_MATCH, ['permission_id' => $item['permission_id']]);
        }

        // 迴圈: 看新增或修改
        $inputPermissions->each(function ($item) use (&$edited_by, &$roleId, &$permissions, &$rolePermissionMappings) {
            $item['role_id'] = $roleId;
            $item['edited_by'] = $edited_by;

            $isExists = $rolePermissionMappings->first(function ($v) use (&$item) {
                return ($v->role_id == $item['role_id']) && ($v->permission_id == $item['permission_id']);
            });
            ! $isExists ? 
            $this->rolePermissionMappingRepository->create($item) : // 新增
            $this->rolePermissionMappingRepository->update($item, $isExists->id); // 修改
        });

        $data = $this->getByRoleId($roleId);

        return $this->response('', null, $data);
    }

    public function destroy($roleId)
    {
        $jwtPayload = $this->getJWTPayload();

        // curl User Module: $roleId 其 ugid
        // return 400: ugid 為 null
        $ugid = 2;
        
        // return 400: 非上帝視角 且 ugid 經比對不同者。不可修刪除 group 的 role
        if ($jwtPayload['user_group_id'] !== 1 && $jwtPayload['user_group_id'] != $ugid)
            return $this->setStatusCode(400)->response('', PermissionError::PERMISSION_DENIED, []);

        $this->rolePermissionMappingRepository->delete($roleId);
        return $this->response("Success", null, []);
    }

    public function getByRoleId($roleId)
    {
        $rolePermissionMappings = $this->rolePermissionMappingRepository->indexSelf($roleId);
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
