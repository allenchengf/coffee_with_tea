<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\CdnWasBatchEdited;
use Hiero7\Enums\InternalError;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use App\Http\Controllers\Controller;
use Hiero7\Services\CdnProviderService;
use App\Http\Requests\CdnProviderRequest as Request;
use DB;
/**
 * Class CdnProviderController
 * @package App\Http\Controllers\Api\v1
 */
class CdnProviderController extends Controller
{
    protected $cdnProviderService;
    /**
     * CdnProviderController constructor.
     */
    public function __construct(CdnProviderService $cdnProviderService)
    {
        $this->cdnProviderService = $cdnProviderService;
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
            'status' => 'active'
        ]);
        $cdnProvider = $cdnProvider->create($request->all());
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
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        DB::beginTransaction();
        $cdnProvider->update($request->only('name','ttl', 'edited_by'));
        $cdn = Cdn::where('cdn_provider_id', $cdnProvider->id)->pluck('provider_record_id')->all();
        if(!empty($cdn)){
            $BatchEditedDnsProviderRecordResult = $this->cdnProviderService->updateDnsProviderTTL($cdnProvider, $cdn);
            if (array_key_exists('errors', $BatchEditedDnsProviderRecordResult[0])) {
                DB::rollback();
                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }
        }
        DB::commit();

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
        $recordList =[];
        $status = $request->get('status')?'active':'stop';
        DB::beginTransaction();
        $cdnProvider->update(['status' => $status, 'edited_by' => $request->get('edited_by')]);
        $cdn = Cdn::where('cdn_provider_id', $cdnProvider->id)->with('locationDnsSetting')->get();
        foreach ($cdn as $k => $v) {
            $check = Cdn::where('provider_record_id',$v['provider_record_id'])->where('default', 1)->where('cdn_provider_id', $cdnProvider->id)->get();
            if (count($check) > 0){
                $recordList[] = $v['provider_record_id'];
                if (isset($v['locationDnsSetting']['provider_record_id'])) {
                    $recordList[] = $v['locationDnsSetting']['provider_record_id'];
                }
            }
        }
        $recordList = array_filter($recordList);
        if (!empty($recordList)) {
            $BatchEditedDnsProviderRecordResult = $this->cdnProviderService->updateDnsProviderStatus($recordList, $status);
            if (array_key_exists('errors', $BatchEditedDnsProviderRecordResult[0])) {
                DB::rollback();
                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }
        }
        if ($status == 'stop') {
            $cdnProvider = CdnProvider::with('domains')->where('id', $cdnProvider->id)->get();
            $this->cdnProviderService->changeDefaultCDN($cdnProvider);
        }
        DB::commit();
        return $this->response();
    }

    public function checkDefault(Request $request, CdnProvider $cdnProvider)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
        ]);

        $cdnProvider = CdnProvider::with('domains')->where('id', $cdnProvider->id)->get();
        $defaultInfo = $this->cdnProviderService->cdnDefaultInfo($cdnProvider);
        return $this->response('', null, $defaultInfo);
    }
}
