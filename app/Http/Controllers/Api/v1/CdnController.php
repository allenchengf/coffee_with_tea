<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\CdnWasCreated;
use App\Events\CdnWasDelete;
use App\Http\Controllers\Controller;
use App\Http\Requests\CdnCreateRequest;
use App\Http\Requests\CdnDeleteRequest;
use App\Http\Requests\CdnUpdateRequest;
use Carbon\Carbon;
use DB;
use Hiero7\Enums\InternalError;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Services\CdnService;
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CdnController extends Controller
{
    use OperationLogTrait;

    protected $cdnService;

    /**
     * CdnController constructor.
     *
     * @param \Hiero7\Services\CdnService $cdnService
     */
    public function __construct(CdnService $cdnService)
    {
        $this->cdnService = $cdnService;
        $this->setCategory(config('logging.category.cdn'));
    }

    /**
     * @param \Hiero7\Models\Domain $domain
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function index(Domain $domain)
    {
        $result = $domain->cdns()->with('CdnProvider')->orderBy('created_at', 'asc')->get();

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    /**
     * @param \App\Http\Requests\CdnCreateRequest $request
     * @param \Hiero7\Models\Domain $domain
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function store(CdnCreateRequest $request, Domain $domain)
    {
        if (!$domain->cdns()->exists()) {

            $request->merge(['default' => true]);
        }

        DB::beginTransaction();

        $cdn = $domain->cdns()->create($this->cdnService->formatRequest($request, $this->getJWTPayload()['uuid']));

        if ($cdn and $request->input('default')) {

            $recordId = event(new CdnWasCreated($domain, $cdn))[0];

            if (!$recordId) {

                DB::rollback();

                return $this->setStatusCode(409)->response("", InternalError::DNSPOD_INSERT_ERROR, []);
            }

            DB::commit();

            $cdn->update(['provider_record_id' => $recordId]);

        }

        DB::commit();

        $this->setChangeTo($cdn->fresh()->saveLog())->createOperationLog(); // SaveLog

        return $this->setStatusCode(200)->response('success', null, $cdn);

    }

    /**
     * @param \App\Http\Requests\CdnUpdateRequest $request
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function updateDefault(CdnUpdateRequest $request, Domain $domain, Cdn $cdn)
    {
        $this->setPortalLogByDomain($domain,  $cdn);
        $this->setChangeFrom($cdn->saveLog());

        $error = $this->cdnService->changeDefaultToTrue($domain, $cdn, $request->edited_by);

        if (!$error) {
            return $this->setStatusCode(409)->response(
                "",
                InternalError::DNSPOD_UPDATE_ERROR
            );
        }

        $this->setChangeTo($cdn->saveLog())->createOperationLog();
        $this->saveForPortalLog();
        return $this->response('', null, $cdn);
    }

    /**
     * @param \App\Http\Requests\CdnUpdateRequest $request
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function updateCname(CdnUpdateRequest $request, Domain $domain, Cdn $cdn)
    {
        $this->setChangeFrom($cdn->saveLog());
        DB::beginTransaction();

        $this->cdnService->modifyCNAME($request);

        $cdn->update($request->only('cname', 'edited_by'));

        if (!$this->cdnService->changeDnsProviderCname($domain, $cdn)) {
            DB::rollback();

            return $this->setStatusCode(409)->response("", InternalError::DNSPOD_UPDATE_ERROR, []);
        }

        $this->cdnService->batchChangeDnsCnameForLocationSetting($cdn);

        DB::commit();

        $this->setChangeTo($cdn->saveLog())->createOperationLog();

//        Redis::

        return $this->setStatusCode(200)->response('success', null, $cdn);
    }

    /**
     * @param \App\Http\Requests\CdnDeleteRequest $request
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function destroy(CdnDeleteRequest $request, Domain $domain, Cdn $cdn)
    {
        event(new CdnWasDelete($cdn));

        $this->setChangeFrom($cdn->saveLog())->createOperationLog();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
