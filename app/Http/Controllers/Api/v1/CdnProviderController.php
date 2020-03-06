<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CdnProviderRequest as Request;
use DB;
use Hiero7\Enums\InputError;
use Hiero7\Enums\InternalError;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Services\CdnProviderService;
use Hiero7\Services\CdnService;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CdnProviderController
 * @package App\Http\Controllers\Api\v1
 */
class CdnProviderController extends Controller
{
    use OperationLogTrait;
    protected $cdnProviderService, $cdnService, $dnsPodRecordSyncService;
    protected $status;
    /**
     * CdnProviderController constructor.
     */
    public function __construct(CdnProviderService $cdnProviderService, CdnService $cdnService, DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->cdnProviderService      = $cdnProviderService;
        $this->cdnService              = $cdnService;
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;

        $this->setCategory(config('logging.category.cdn_provider'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_group_id = $this->getUgid($request);
        $result        = $this->cdnProviderService->getCdnProvider($user_group_id);

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, CdnProvider $cdnProvider)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $createData = $request->only('name', 'ttl', 'user_group_id', 'url') + ['status' => 'active'];

        $cdnProvider = $cdnProvider->create($createData);

        $this->setChangeTo($cdnProvider->fresh()->saveLog())->createOperationLog();

        return $this->response('', null, $cdnProvider);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Hiero7\Models\CdnProvider  $cdnProvider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CdnProvider $cdnProvider)
    {
        $this->setChangeFrom($cdnProvider->saveLog());

        $recordList = [];
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $user_group_id = $this->getUgid($request);

        if ($user_group_id != $cdnProvider->user_group_id) {
            return $this->setStatusCode(403)->response('', PermissionError::THIS_GROUP_ID_NOT_MATCH, '');
        }

        DB::beginTransaction();

        $oldTTL = (int) $cdnProvider->ttl;

        $cdnProvider->update($request->only('name', 'ttl', 'edited_by', 'url'));

        $newTTL = (int) $cdnProvider->ttl;

        if ($oldTTL != $newTTL) {
            $cdns = Cdn::where('cdn_provider_id', $cdnProvider->id)->with('locationDnsSetting')->get();

            // 取得這次所有異動的 Record
            $allRecord = [];

            foreach ($cdns as $cdn) {
                $records   = $this->cdnService->getRecordByCDN($cdn);
                $allRecord = array_merge($allRecord, $records);
            }

            // 執行修改 Record 狀態
            $result = $this->dnsPodRecordSyncService->syncRecord([], $allRecord, []);
        }

        DB::commit();

        $this->cdnProviderService->checkWhetherStopScannable($cdnProvider, $request->get('edited_by'));

        $this->setChangeTo($cdnProvider->saveLog())->createOperationLog();

        return $this->response("Success", null, $cdnProvider);
    }

    /**
     * @param Request $request
     * @param CdnProvider $cdnProvider
     * @return $this
     */
    public function changeStatus(Request $request, CdnProvider $cdnProvider)
    {
        $this->setChangeFrom($cdnProvider->saveLog());

        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $recordList = [];
        $status = $request->get('status') ? 'active' : 'stop';

        DB::beginTransaction();
        $oldStatus = (int) $cdnProvider->status;

        $cdnProvider->update(['status' => $status, 'edited_by' => $request->get('edited_by')]);

        $newStatus = (int) $cdnProvider->status;

        if ($oldStatus != $newStatus) {
            $cdns = Cdn::where('cdn_provider_id', $cdnProvider->id)->with('locationDnsSetting')->get();

            $allRecord = [];

            foreach ($cdns as $cdn) {
                $records   = $this->cdnService->getRecordByCDN($cdn);
                $allRecord = array_merge($allRecord, $records);
            }

            $result = $this->dnsPodRecordSyncService->syncRecord([], $allRecord, []);
        }

        if ($status == 'stop') {
            $cdnProviderCollection = CdnProvider::with('domains')->where('id', $cdnProvider->id)->get();
            $this->cdnProviderService->changeDefaultCDN($cdnProviderCollection);
        }
        DB::commit();

        $this->cdnProviderService->checkWhetherStopScannable($cdnProvider, $request->get('edited_by'));

        $this->setChangeTo($cdnProvider->fresh()->saveLog())->createOperationLog();

        return $this->response();
    }

    /**
     * 整理出 有被更改 且 pod 上也需要更動的
     *
     * @param Collection $cdns
     * @return array 要打 pod 的 record ID list
     */
    private function getRecordList(Collection $cdns)
    {
        $recordList = [];

        foreach ($cdns as $cdnModel) {
            if ($cdnModel['default'] == 1) {
                $recordList[] = $cdnModel['provider_record_id'];
            }

            if (isset($cdnModel['locationDnsSetting'])) {
                foreach ($cdnModel['locationDnsSetting'] as $locationDnsSetting) {
                    $recordList[] = $locationDnsSetting['provider_record_id'];
                }
            }
        }

        return $recordList;
    }

    /**
     * Scannable 的 開啟 或 關閉
     *
     * 若 status = stop 或 url = null 的時候都會直接回傳 error
     * @param Request $request
     * @param CdnProvider $cdnProvider
     * @return void
     */
    public function changeScannable(Request $request, CdnProvider $cdnProvider)
    {
        $this->setChangeFrom($cdnProvider->saveLog());

        $scannable = $request->get('scannable') ? true : false;

        if ($scannable) {

            if (!$cdnProvider->status && empty($cdnProvider->url)) {
                return $this->setStatusCode(400)->response('', InputError::THIS_CDNPROVIDER_STATUS_AND_URL_ARE_UNAVAILABLE, []);
            }

            if (!$cdnProvider->status) {
                return $this->setStatusCode(400)->response('', InputError::THIS_CDNPROVIDER_STATUS_IS_STOP, []);
            }

            if (empty($cdnProvider->url)) {
                return $this->setStatusCode(400)->response('', InputError::THIS_CDNPROVIDER_URL_IS_NULL, []);
            }
        }
        $cdnProvider->update(['scannable' => $scannable, 'edited_by' => $request->get('edited_by')]);

        $this->setChangeTo($cdnProvider->saveLog())->createOperationLog();
        return $this->response('', null, $cdnProvider);
    }

    public function checkDefault(CdnProvider $cdnProvider)
    {
        $defaultInfo = [
            'have_multi_cdn' => [],
            'only_default'   => [],
        ];

        if ($cdnProvider->status) {
            $defaultInfo = $this->cdnProviderService->cdnDefaultInfo($cdnProvider);
        }

        $defaultInfo['be_used'] = $cdnProvider->domains->keyBy('name')->keys();

        return $this->response('', null, $defaultInfo);
    }

    public function destroy(CdnProvider $cdnProvider)
    {
        $this->setChangeFrom($cdnProvider->saveLog());

        $payload = $this->getJWTPayload();

        $errorCode = null;

        if ($payload['user_group_id'] != $cdnProvider->user_group_id) {
            return $this->setStatusCode(403)->response('', PermissionError::THIS_GROUP_ID_NOT_MATCH, '');
        } else if ($cdnProvider->cdns->isEmpty()) {
            $cdnProvider->delete();

            $this->createOperationLog();
        } else {
            $errorCode = InputError::CANT_DELETE_THIS_CDN_PROVIDER;
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response('', $errorCode);
    }

    /**
     * @param \Hiero7\Models\CdnProvider $cdnProvider
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailedInfo(CdnProvider $cdnProvider)
    {
        $result = $cdnProvider::select(['name', 'status'])->withCount([
            'domains as default_domains_count' => function (Builder $query) {
                $query->where('default', '=', 1);
            },
        ])->where('user_group_id', $this->getJWTPayload()['user_group_id'])->get();

        return $this->setStatusCode($result ? 200 : 400)->response('', '', $result);
    }
}
