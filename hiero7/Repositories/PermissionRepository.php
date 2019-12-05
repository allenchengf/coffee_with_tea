<?php

namespace Hiero7\Repositories;

use Hiero7\Models\Permission;

class PermissionRepository
{
    protected $permission;
    
    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    public function index()
    {
        return $this->permission->all();
    }
}