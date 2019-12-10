<?php

namespace Hiero7\Repositories;

use Hiero7\Models\RolePermissionMapping;

class RolePermissionMappingRepository
{
    protected $rolePermissionMapping;
    
    public function __construct(RolePermissionMapping $rolePermissionMapping)
    {
        $this->rolePermissionMapping = $rolePermissionMapping;
    }

    public function index()
    {
        return $this->rolePermissionMapping->all();
    }

    public function indexSelf($role_id)
    {
        $data = $this->rolePermissionMapping->where('role_id', $role_id)->get();
        $data->each(function ($item, $i) use (&$data) {
            $data[$i]['actions'] = json_decode($item['actions'], true);
        });
        return $data;
    }

    public function create(array $data)
    {
        $data['actions'] = $this->getActionsJson($data);
        return $this->rolePermissionMapping->create($data);
    }

    public function update($data, $id)
    {
        $actions = $this->getActionsJson($data);

        return $this->rolePermissionMapping->where('id', $id)->update([
            "actions" => $actions
        ]);
    }

    public function delete($role_id)
    {
        return $this->rolePermissionMapping->where('role_id', $role_id)->delete();        
    }

    private function getActionsJson($data)
    {
        $actions = $data['actions'];
        return json_encode([
            'read'   => $actions['read'] ? 1 : 0,
            'create' => $actions['create'] ? 1 : 0,
            'update' => $actions['update'] ? 1 : 0,
            'delete' => $actions['delete'] ? 1 : 0,
        ]);
    }
}