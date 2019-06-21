<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainGroupRequest;
use Hiero7\Enums\InputError;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\{DomainGroup,Domain};
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
/**
 * Grouping 頁面
 *
 * @param DomainGroupRequest $request
 * @return void
 */
    public function index(DomainGroupRequest $request)
    {
        $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->index($this->userGroupId);

        return $this->response('', null, $result);
    }
/**
 * Groping/General 頁面
 *
 * @param DomainGroupRequest $request
 * @param DomainGroup $domainGroup
 * @return void
 */
    public function indexByDomainGroupId(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->indexByDomainGroupId($domainGroup);

        return $this->response('', null, $result);
    }

    public function create(DomainGroupRequest $request)
    {
        $request = $this->formatRequestAndThis($request);

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

    public function createDomainToGroup(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $request = $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->createDomainToGroup($request->all(),$domainGroup);

        if ($result == false) {
            $this->error = InputError::DOMAIN_CDNPROVIDER_DIFFERENT;
            $result = [];
        }
        
        // $result = $this->domainGroupService->indexByDomainGroupId($domainGroup);
        
        return $this->response($this->message, $this->error, $result);
    }

    public function edit(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $request = $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->edit($request->all(), $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
            $result = [];
        }

        $result = $this->domainGroupService->index($this->userGroupId);

        return $this->response($this->message, $this->error, $result);
    }

    public function destroy(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $this->formatRequestAndThis($request);
        $this->domainGroupService->destroy($domainGroup->id);

        return $this->response();
    }
/**
 * Groping/General 頁面
 *
 * @param DomainGroupRequest $request
 * @param Domain $domain
 * @return void
 */
    public function destroyByDomainId(DomainGroupRequest $request, DomainGroup $domainGroup ,Domain $domain)
    {
        $this->formatRequestAndThis($request);
        $this->domainGroupService->destroyByDomainId($domainGroup->id,$domain->id);

        return $this->response();
    }

    private function formatRequestAndThis(DomainGroupRequest $request)
    {
        $this->userGroupId = $this->getUgid($request);
        $this->uuid = $this->getJWTPayload()['uuid'];

        return $request->merge([
            'user_group_id' => $this->userGroupId,
            'edited_by' => $this->uuid,
        ]);
    }
}
