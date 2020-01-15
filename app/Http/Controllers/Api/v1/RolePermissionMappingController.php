<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolePermissionMappingRequest;
use Hiero7\Services\UserModuleService;
use Hiero7\Repositories\{RolePermissionMappingRepository, PermissionRepository};
use Hiero7\Models\RolePermissionMapping;
use Hiero7\Traits\JwtPayloadTrait;
use Hiero7\Enums\{InputError, PermissionError};

class RolePermissionMappingController extends Controller
{
    use JwtPayloadTrait;

    public function __construct(
        RolePermissionMappingRepository $rolePermissionMappingRepository,
        PermissionRepository $permissionRepository,
        UserModuleService $userModuleService
    )
    {
        $this->rolePermissionMappingRepository = $rolePermissionMappingRepository;
        $this->permissionRepository = $permissionRepository;
        $this->userModuleService = $userModuleService;
    }

    public function indexSelf()
    {
        $jwtPayload = $this->getJWTPayload();
        $roleId = $jwtPayload['role_id'];
        $rolePermissionMappings = $this->getByRoleId($roleId);
        if (! $rolePermissionMappings) {
            return $this->setStatusCode(400)->response('', PermissionError::YOU_DONT_HAVE_ROLE_PERMISSION, []);
        }
        return $this->response("Success", null, $rolePermissionMappings);
    }

    public function indexByRoleId(RolePermissionMappingRequest $request, $roleId)
    {
        $jwtPayload = $this->getJWTPayload();
        
        // return 400: 不可觀看別 group 的 role，除非上帝視角 ugid: 1
        if ($jwtPayload['user_group_id'] !== 1) {
            // curl User Module: 其 $roleId 之 ugid
            $ugid = $this->userModuleService->getUgidByRoleId($request, $roleId)['data']['user_group_id'];
            if ($jwtPayload['user_group_id'] != $ugid) {
                return $this->setStatusCode(400)->response('', PermissionError::ROLE_PERMISSION_DENIED, []);
            }
        }

        $rolePermissionMappings = $this->getByRoleId($roleId);
        if (! $rolePermissionMappings) {
            return $this->setStatusCode(400)->response('', PermissionError::YOU_DONT_HAVE_ROLE_PERMISSION, []);
        }

        return $this->response("Success", null, $rolePermissionMappings);
    }

    public function upsert(RolePermissionMappingRequest $request, $roleId)
    {
        $jwtPayload = $this->getJWTPayload();
        $edited_by = $jwtPayload['uuid'];

        // return 400: 不可增改別 group 的 role，除非上帝視角 ugid: 1
        if ($jwtPayload['user_group_id'] !== 1) {
            // curl User Module: 其 $roleId 之 ugid
            $ugid = $this->userModuleService->getUgidByRoleId($request, $roleId)['data']['user_group_id'];
            if ($jwtPayload['user_group_id'] != $ugid) {
                return $this->setStatusCode(400)->response('', PermissionError::ROLE_PERMISSION_DENIED, []);
            }
        }

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

        $rolePermissionMappings = $this->getByRoleId($roleId);
        if (! $rolePermissionMappings) {
            return $this->setStatusCode(400)->response('', PermissionError::YOU_DONT_HAVE_ROLE_PERMISSION, []);
        }

        return $this->response('', null, $rolePermissionMappings);
    }

    public function destroy(RolePermissionMappingRequest $request, $roleId)
    {
        $jwtPayload = $this->getJWTPayload();

        // return 400: 不可刪除別 group 的 role，除非上帝視角 ugid: 1
        if ($jwtPayload['user_group_id'] !== 1) {
            // curl User Module: 其 $roleId 之 ugid
            $ugid = $this->userModuleService->getUgidByRoleId($request, $roleId)['data']['user_group_id'];
            if ($jwtPayload['user_group_id'] != $ugid) {
                return $this->setStatusCode(400)->response('', PermissionError::ROLE_PERMISSION_DENIED, []);
            }
        }

        $this->rolePermissionMappingRepository->delete($roleId);
        return $this->response("Success", null, []);
    }

    public function getByRoleId($roleId)
    {
        $rolePermissionMappings = $this->rolePermissionMappingRepository->indexSelf($roleId);
        if (! $rolePermissionMappings || $rolePermissionMappings->isEmpty()) {
            return null;
        }

        $permissions = $this->permissionRepository->index();

        $permissions->each(function ($p, $iP) use (&$roleId, &$rolePermissionMappings) {
            $rolePermissionMappings->each(function ($rpm, $iRpm) use (&$p, &$rolePermissionMappings) {
                if ($rolePermissionMappings[$iRpm]['permission_id'] == $p['id'] ) {
                    $rolePermissionMappings[$iRpm]['permission'] = $p;
                    return false;
                }
            });

            // 補假ㄉ Dashboard (permission_id: 9)
            if ($p['id'] == 9) {
                $rolePermissionMappings->push([
                    'id' => null,
                    'role_id' => $roleId,
                    'permission_id' => $p['id'],
                    'actions' => ["read" => 1,"create" => 0,"update" => 0,"delete" => 0],
                    'edited_by' => null,
                    'permission' => $p,
                ]);
            }
        });

        return $rolePermissionMappings;
    }
}
