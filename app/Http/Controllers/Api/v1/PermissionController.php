<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hiero7\Repositories\PermissionRepository;

class PermissionController extends Controller
{
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function index()
    {
        $data = $this->permissionRepository->index();
        return $this->response("Success", null, $data);
    }
}
