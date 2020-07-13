<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainGroupRequest;
use Carbon\Carbon;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Domain;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\LocationNetwork;
use Hiero7\Services\CdnService;
use Hiero7\Services\DomainGroupService;
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DomainGroupController extends Controller
{
    use OperationLogTrait;

    protected $domainGroupService;
    protected $message;
    protected $error;
    protected $userGroupId;
    protected $uuid;

    public function __construct(DomainGroupService $domainGroupService, CdnService $cdnService)
    {
        $this->domainGroupService = $domainGroupService;
        $this->cdnService         = $cdnService;
        $this->setCategory(config('logging.category.domain_group'));

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
            $result      = [];
        }

        if ($result == 'NoneCdn') {
            $this->error = PermissionError::THIS_DOMAIN_DONT_HAVE_ANY_CDN;
            $result      = [];
        }

        if ($result == 'differentGroup') {
            $this->error = InputError::PARAMETERS_IN_DIFFERENT_USERGROUP;
            $result      = [];
        }

        if (gettype($result) == 'object') {
            $log = $result[0]->domainGroup()->first()->saveLog();
            $log += $this->domainLogFormat($result[0]);
            $this->setChangeTo($log)->createOperationLog();
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error, $result);
    }

    public function createDomainToGroup(DomainGroupRequest $request, DomainGroup $domainGroup)
    {
        $request = $this->formatRequestAndThis($request);

        if (!$domainGroup->mapping->where('domain_id', $request['domain_id'])->isEmpty()) {
            return $this->setStatusCode(400)->response($this->message, InputError::DOMAIN_ALREADY_EXIST_GROUP, []);
        }

        if (!$targetDomain = Domain::find($request->domain_id)) {
            return $this->setStatusCode(400)->response($this->message, InputError::DOMAIN_NOT_EXIST, []);
        }

        $result = $this->domainGroupService->createDomainToGroup($request, $domainGroup);

        if ($result == false) {
            $this->error = InputError::DOMAIN_CDNPROVIDER_DIFFERENT;
            $result      = [];
        } else {
            if ($result == 'cdnError' || $result == 'iRouteError') {
                $this->error = InternalError::INTERNAL_SERVICE_ERROR;
                $result      = [];
            } else {
                $result->domain;
                $result->domainGroup;

                $log = $result->domainGroup()->first()->saveLog();
                $log += $this->domainLogFormat($result->domain);

                $this->setChangeTo($log)->createOperationLog();
            }
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
        $this->setChangeFrom($domainGroup->saveLog());

        $request = $this->formatRequestAndThis($request);

        $result = $this->domainGroupService->edit($request, $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
        } else {
            $this->setChangeTo($domainGroup->fresh()->saveLog())->createOperationLog();
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error,
            $this->error ? null : $domainGroup);
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
        $this->setChangeFrom($domainGroup->saveLog());

        $this->formatRequestAndThis($request);
        $result = $this->domainGroupService->destroy($request, $domainGroup);

        if ($result == false) {
            $this->error = PermissionError::PERMISSION_DENIED;
        } else {
            $this->createOperationLog();
        }

        return $this->setStatusCode($this->error ? 400 : 200)->response($this->message, $this->error);
    }

    /**
     *  從 Group 移除特定 Domain
     *
     * @param DomainGroupRequest $request
     * @param Domain $domain
     * @return void
     */
    public function destroyByDomainId(DomainGroupRequest $request, DomainGroup $domainGroup, Domain $domain)
    {
        $log = $domainGroup->saveLog() + ['domain' => $domain->name];
        $this->setChangeFrom($log);

        $this->formatRequestAndThis($request);
        $result = $this->domainGroupService->destroyByDomainId($request, $domainGroup, $domain);

        if ($result == false) {
            $this->error = PermissionError::CANT_DELETE_LAST_DOMAIN;
        } else {
            unset($log['domain']);
            $this->setChangeTo($domainGroup->fresh()->saveLog())->createOperationLog();
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
        $this->setPortalLogByGroup($domainGroup, $request->cdn_provider_id);

        $log            = $domainGroup->saveLog();
        $log['default'] = $this->domainLogFormat($domainGroup->domains()->first())['default'];

        $this->setChangeFrom($log);

        $domainModel = $domainGroup->domains;

        $redisKey = "changeCDNStatusByGroupId_$domainGroup->id";

        foreach ($domainModel as $domain) {

            // 暫存 Change CDN 的狀態
            Redis::set($redisKey, true, 'EX', 60);

            $cdn    = $domain->cdns()->where('cdns.cdn_provider_id', $request->cdn_provider_id)->first();
            $result = $this->cdnService->changeDefaultToTrue($domain, $cdn, $this->getJWTPayload()['uuid']);
        }

        if ($result == false) {
            $this->error = InternalError::INTERNAL_ERROR;
            $result      = [];
        } else {

            $log['default'] = $this->domainLogFormat($domainGroup->domains()->first())['default'];

            $this->saveForPortalLog();

            $this->setChangeTo($log)->createOperationLog();
        }

        // 移除 Change CDN 的狀態
        Redis::del($redisKey);

        return $this->setStatusCode($this->error ? 409 : 200)->response($this->message, $this->error, $result);
    }

    public function updateRouteCdn(
        DomainGroupRequest $request,
        DomainGroup $domainGroup,
        LocationNetwork $locationNetwork
    ) {
        $this->setCategory(config('logging.category.iroutecdn'));
        $log['cdnProvider'] = $this->getOriginCdnProvider($domainGroup, $locationNetwork)->name;
        $log['domain']      = $domainGroup->name;
        $log['region']      = $locationNetwork->saveLog();

        $this->setChangeFrom($log);

        $this->formatRequestAndThis($request);
        $result = $this->domainGroupService->updateRouteCdn($domainGroup, $locationNetwork, $request->cdn_provider_id,
            $request->edited_by);

        unset($log['region']);
        $log['cdnProvider'] = $this->getOriginCdnProvider($domainGroup, $locationNetwork)->name;
        $this->setChangeTo($log)->createOperationLog();

        return $this->handleResponse($result);
    }

    private function formatRequestAndThis(DomainGroupRequest $request)
    {
        $this->userGroupId = $this->getUgid($request);
        $this->uuid        = $this->getJWTPayload()['uuid'];

        return $request->merge([
            'user_group_id' => $this->userGroupId,
            'edited_by'     => $this->uuid,
        ]);
    }

    protected function handleResponse($result)
    {
        if (method_exists($result, 'getStatusCode') && $result->getStatusCode() == 404) {
            return abort(404);
        }

        if (method_exists($result, 'getCode')) {
            return $this->response($result->getMessage(), $result->getCode(), null)->setStatusCode(400);
        }

        return $this->response('Success', null, $result);
    }

    private function domainLogFormat(Domain $domain)
    {
        $log            = [];
        $log['domain']  = $domain->name;
        $log['default'] = $domain->getDefaultCdnProvider()->name;
        return $log;
    }

    /**
     * @param DomainGroup $domainGroup
     * @param LocationNetwork $locationNetwork
     */
    private function getOriginCdnProvider(DomainGroup $domainGroup, LocationNetwork $locationNetwork)
    {
        $domain = $domainGroup->domains()->first();

        $locationDnsSetting = $domain->locationDnsSettings()->where('location_networks_id',
            $locationNetwork->id)->first();

        if ($locationDnsSetting) {
            $cdnProvider = $locationDnsSetting->cdn()->first()->cdnProvider()->first();

        } else {
            $cdnProvider = $domain->getDefaultCdnProvider()->first();
        }

        return $cdnProvider;
    }
}
