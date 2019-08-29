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
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CdnProviderController
 * @package App\Http\Controllers\Api\v1
 */
class CdnProviderController extends Controller
{
    use OperationLogTrait;
    protected $cdnProviderService;
    protected $status;
    /**
     * CdnProviderController constructor.
     */
    public function __construct(CdnProviderService $cdnProviderService)
    {
        $this->cdnProviderService = $cdnProviderService;
        $this->status = (env('APP_ENV') !== 'testing') ?? false;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_group_id = $this->getUgid($request);
        $result = $this->cdnProviderService->getCdnProvider($user_group_id);

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
            'status' => 'active',
            'scannable' => 0,
        ]);
        $cdnProvider = $cdnProvider->create($request->all());
        $this->createEsLog($this->getJWTPayload()['sub'], "CDN", "create", "CDN Provider");
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
        $recordList = [];
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $user_group_id = $this->getUgid($request);

        if ($user_group_id != $cdnProvider->user_group_id) {
            return $this->setStatusCode(403)->response('', PermissionError::THIS_GROUP_ID_NOT_MATCH, '');
        }

        DB::beginTransaction();
        $cdnProvider->update($request->only('name', 'ttl', 'edited_by', 'url'));
        $cdn = Cdn::where('cdn_provider_id', $cdnProvider->id)->with('locationDnsSetting')->get();
        
        $recordList = array_filter($this->getRecordList($cdn));

        if (!empty($recordList)) {
            $BatchEditedDnsProviderRecordResult = $this->cdnProviderService->updateCdnProviderTTL($cdnProvider, $recordList);
            if (array_key_exists('errors', $BatchEditedDnsProviderRecordResult[0])) {
                DB::rollback();
                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }
        }
        DB::commit();
        $this->cdnProviderService->checkWhetherStopScannable($cdnProvider, $request->get('edited_by'));
        $this->createEsLog($this->getJWTPayload()['sub'], "CDN", "update", "CDN Provider");
        return $this->response("Success", null, $cdnProvider);
    }

    /**
     * @param Request $request
     * @param CdnProvider $cdnProvider
     * @return $this
     */
    public function changeStatus(Request $request, CdnProvider $cdnProvider)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $recordList = [];
        $status = $request->get('status') ? 'active' : 'stop';
        DB::beginTransaction();
        $cdnProvider->update(['status' => $status, 'edited_by' => $request->get('edited_by')]);
        $cdn = Cdn::where('cdn_provider_id', $cdnProvider->id)->with('locationDnsSetting')->get();

        $recordList = array_filter($this->getRecordList($cdn));

        if (!empty($recordList)) {
            $BatchEditedDnsProviderRecordResult = $this->cdnProviderService->updateCdnProviderStatus($recordList, $status);
            if (array_key_exists('errors', $BatchEditedDnsProviderRecordResult[0])) {
                DB::rollback();
                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }
        }
        if ($status == 'stop') {
            $cdnProviderCollection = CdnProvider::with('domains')->where('id', $cdnProvider->id)->get();
            $this->cdnProviderService->changeDefaultCDN($cdnProviderCollection);
        }
        DB::commit();
        $this->cdnProviderService->checkWhetherStopScannable($cdnProvider, $request->get('edited_by'));
        $this->createEsLog($this->getJWTPayload()['sub'], "CDN", "change", "CDN Provider status");
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
        return $this->response('', null, $cdnProvider);
    }

    public function checkDefault(CdnProvider $cdnProvider)
    {
        $defaultInfo = [
            'have_multi_cdn' => [],
            'only_default' => [],
        ];

        if ($cdnProvider->status){
            $defaultInfo = $this->cdnProviderService->cdnDefaultInfo($cdnProvider);
            $this->createEsLog($this->getJWTPayload()['sub'], "CDN", "check", "CDN Provider");
        }

        return $this->response('', null, $defaultInfo);
    }

    public function destroy(Request $request, CdnProvider $cdnProvider)
    {
        $user_group_id = $this->getUgid($request);

        if ($user_group_id != $cdnProvider->user_group_id) {
            return $this->setStatusCode(403)->response('', PermissionError::THIS_GROUP_ID_NOT_MATCH, '');
        }

        $error = $this->cdnProviderService->deleteCDNProvider($cdnProvider);
        if ($error) {
            return $this->setStatusCode(409)->response(
                'please contact the admin',
                InternalError::INTERNAL_ERROR
            );
        }
        $this->createEsLog($this->getJWTPayload()['sub'], "CDN", "delete", "CDN Provider");
        return $this->response();
    }
}
