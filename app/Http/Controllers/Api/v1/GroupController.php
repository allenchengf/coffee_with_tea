<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hiero7\Services\GroupService;

class GroupController extends Controller
{
    protected $groupService;
    
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function index()
    {
        $result = $this->groupService->index();
        return $this->setStatusCode($result ? 200 : 400)->response(
            '',
            '', $result
        );
    }

    public function create(Request $request)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $result = $this->groupService->create($request->all());
        return $this->setStatusCode($result ? 200 : 400)->response(
            '',
            '', $result
        );
    }
}
