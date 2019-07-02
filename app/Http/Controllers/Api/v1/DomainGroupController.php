<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainGroupRequest;
use Hiero7\Enums\{InputError,InternalError};
use Hiero7\Enums\PermissionError;
use Hiero7\Models\{DomainGroup,Domain};
use Hiero7\Services\{DomainGroupService,CdnService};
use Illuminate\Http\Request;

class DomainGroupController extends Controller
{
    protected $domainGroupService;
    protected $message;
    protected $error;
    protected $userGroupId;
    protected $uuid;

    public function __construct(DomainGroupService $domainGroupService, CdnService $cdnService)
    {
        $this->domainGroupService = $domainGroupService;
        $this->cdnService = $cdnService;
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

    public function indexGroupIroute(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->indexGroupIroute($domainGroup);

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

        $result = $this->domainGroupService->create($request);
        if ($result == 'exist') {
            $this->error = InputError::GROUP_EXIST;
            $result = [];
        }

        if($result == 'NoneCdn'){
            $this->error = PermissionError::THIS_DOMAIN_DONT_HAVE_ANY_CDN;
            $result = [];
        }

        if ($result == 'differentGroup') {
            $this->error = InputError::PARAMETERS_IN_DIFFERENT_USERGROUP;
            $result = [];
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    public function createDomainToGroup(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $request = $this->formatRequestAndThis($request);

        if(!$domainGroup->mapping->where('domain_id',$request['domain_id'])->isEmpty()){
            return $this->setStatusCode(400)->response($this->message, InputError::DOMAIN_ALREADY_EXIST_GROUP, []);
        }


        $result = $this->domainGroupService->createDomainToGroup($request,$domainGroup);

        if ($result == false) {
            $this->error = InputError::DOMAIN_CDNPROVIDER_DIFFERENT;
            $result = [];
        }

        if ($result == 'cdnError' || $result == 'iRouteError') {
            $this->error = InternalError::INTERNAL_SERVICE_ERROR;
            $result = [];
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    public function edit(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $request = $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->edit($request, $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
            $result = [];
        }

        $result = $this->domainGroupService->index($this->userGroupId);

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
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
        $result = $this->domainGroupService->destroyByDomainId($domainGroup,$domain);

        if($result ==false){
            $this->error = PermissionError::CANT_DELETE_LAST_DOMAIN;
        }
        
        $result = [];

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    public function changeDefaultCdn(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $domainModel = $domainGroup->domains;

        foreach($domainModel as $domain){
            $cdn = $domain->cdns()->where('cdn_provider_id',$request['cdn_provider_id'])->first();
            $result = $this->cdnService->changeDefaultToTrue($domain,$cdn, $this->getJWTPayload()['uuid']);
        }

        if ($result == false) {
            $this->error = InternalError::INTERNAL_ERROR;
            $result = [];
        }

        return $this->setStatusCode($this->error ? 409 : 200)->response($this->message, $this->error, $result);
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
