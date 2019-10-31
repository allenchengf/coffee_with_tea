<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainGroupRequest;
use Hiero7\Enums\{InputError, InternalError, PermissionError};
use Hiero7\Models\{DomainGroup, Domain, LocationNetwork};
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

    /**
     * get DomainGroup 的 iRoute 列表 function
     *
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function indexGroupIroute(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->indexGroupIroute($domainGroup);

        return $this->response('', null, $result);
    }

    /**
     * 取特定 Group 的 Domain 和 cdn Provider 資訊
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

    /**
     * 純 crete group function
     *
     * @param DomainGroupRequest $request
     * @return void
     */
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
        }else if ($result == 'cdnError' || $result == 'iRouteError') {
            $this->error = InternalError::INTERNAL_SERVICE_ERROR;
            $result = [];
        }else{
            $result->domain;
            $result->domainGroup;
        }


        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    /**
     * 純 修改 Group 
     *
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
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

    /**
     * 純 刪除 Group
     *
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
    public function destroy(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $this->formatRequestAndThis($request);
        $result =  $this->domainGroupService->destroy($request, $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
            $result = [];
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    /**
     *  從 Group 移除特定 Domain
     *
     * @param DomainGroupRequest $request
     * @param Domain $domain
     * @return void
     */
    public function destroyByDomainId(DomainGroupRequest $request, DomainGroup $domainGroup ,Domain $domain)
    {
        $this->formatRequestAndThis($request);
        $result = $this->domainGroupService->destroyByDomainId($request ,$domainGroup,$domain);

        if($result ==false){
            $this->error = PermissionError::CANT_DELETE_LAST_DOMAIN;
        }

        $domain->domainGroup;

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $domain);
    }

    /**
     * 修改 Group 內所有 domain 的 default cdn
     *
     * @param DomainGroupRequest $request
     * @param DomainGroup $domainGroup
     * @return void
     */
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

    public function updateRouteCdn(DomainGroupRequest $request, DomainGroup $domainGroup, LocationNetwork $locationNetwork)
    {
        $this->formatRequestAndThis($request);
        $result = $this->domainGroupService->updateRouteCdn($domainGroup, $locationNetwork, $request->cdn_provider_id, $request->edited_by);

        return $this->handleResponse($result);
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

    protected function handleResponse($result){
        if (method_exists($result, 'getStatusCode') && $result->getStatusCode() == 404)
            return abort(404);        
        if (method_exists($result, 'getCode'))
            return $this->response($result->getMessage(), $result->getCode(), null)->setStatusCode(400);
        return $this->response('Success', null, $result);   
    }
}
