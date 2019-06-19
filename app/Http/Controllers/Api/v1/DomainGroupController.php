<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use Hiero7\Enums\InputError;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\DomainGroup;
use Hiero7\Services\DomainGroupService;
use Illuminate\Http\Request;

class DomainGroupController extends Controller
{
    protected $domainGroupService;
    protected $message;
    protected $error;
    protected $userGroupId;
    protected $uuid;

    public function __construct(DomainGroupService $domainGroupService)
    {
        $this->domainGroupService = $domainGroupService;
    }

    public function index(Request $request)
    {
        $this->formateRequestAndThis($request);

        $result = $this->domainGroupService->index($this->userGroupId);

        return $this->response('', null, $result);
    }

    public function create(Request $request)
    {
        $request = $this->formateRequestAndThis($request);

        $result = $this->domainGroupService->create($request->all());
        if ($result == 'exist') {
            $this->error = InputError::GROUP_EXIST;
            $result = [];
        }

        if ($result == 'differentGroup') {
            $this->error = InputError::PARAMETERS_IN_DIFFERENT_USERGROUP;
            $result = [];
        }

        if ($result == 'done') {
            $result = $this->domainGroupService->index($this->userGroupId);
        }

        return $this->response($this->message, $this->error, $result);
    }

    public function edit(Request $request, DomainGroup $domainGroup)
    {
        $request = $this->formateRequestAndThis($request);

        $result = $this->domainGroupService->edit($request->all(), $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
            $result = [];
        }

        $result = $this->domainGroupService->index($this->userGroupId);

        return $this->response($this->message, $this->error, $result);
    }

    public function destroy(Request $request, DomainGroup $domainGroup)
    {
        $this->formateRequestAndThis($request);
        $this->domainGroupService->destroy($domainGroup->id);

        return $this->response();
    }

    private function formateRequestAndThis(Request $request)
    {
        $this->userGroupId = $this->getUgid($request);
        $this->uuid = $this->getJWTPayload()['uuid'];

        return $request->merge([
            'user_group_id' => $this->userGroupId,
            'edited_by' => $this->uuid,
        ]);
    }
}
