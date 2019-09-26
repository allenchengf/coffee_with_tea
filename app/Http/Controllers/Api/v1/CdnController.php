<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\CdnWasCreated;
use App\Events\CdnWasDelete;
use App\Http\Controllers\Controller;
use App\Http\Requests\CdnCreateRequest;
use App\Http\Requests\CdnUpdateRequest;
use App\Http\Requests\CdnDeleteRequest;
use DB;
use Hiero7\Enums\InternalError;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Services\CdnService;
use Illuminate\Http\Request;

class CdnController extends Controller
{
    protected $cdnService;

    /**
     * CdnController constructor.
     *
     * @param \Hiero7\Services\CdnService $cdnService
     */
    public function __construct(CdnService $cdnService)
    {
        $this->cdnService = $cdnService;
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
     * @param \Hiero7\Models\Domain         $domain
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

            $createdDnsProviderRecordResult = event(new CdnWasCreated($domain, $cdn));

            if (!is_null($createdDnsProviderRecordResult[0]['errorCode']) or array_key_exists('errors',
                $createdDnsProviderRecordResult[0])) {

                DB::rollback();

                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }

            DB::commit();

            $cdn->update(['provider_record_id' => $createdDnsProviderRecordResult[0]['data']['record']['id']]);
        }

        DB::commit();

        return $this->setStatusCode(200)->response('success', null, $cdn);

    }

    /**
     * @param \App\Http\Requests\CdnUpdateRequest $request
     * @param \Hiero7\Models\Domain         $domain
     * @param \Hiero7\Models\Cdn            $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function updateDefault(CdnUpdateRequest $request, Domain $domain, Cdn $cdn)
    {
        $error = $this->cdnService->changeDefaultToTrue($domain, $cdn, $request->edited_by);

        if (!$error) {
            return $this->setStatusCode(409)->response(
                'please contact the admin',
                InternalError::INTERNAL_ERROR
            );
        }
        return $this->response('', null, $cdn);
    }

    /**
     * @param \App\Http\Requests\CdnUpdateRequest $request
     * @param \Hiero7\Models\Domain         $domain
     * @param \Hiero7\Models\Cdn            $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function updateCname(CdnUpdateRequest $request, Domain $domain, Cdn $cdn)
    {
        DB::beginTransaction();

        $this->cdnService->modifyCNAME($request);

        $cdn->update($request->only('cname', 'edited_by'));

        if (!$this->cdnService->changeDnsProviderCname($domain, $cdn)) {
            DB::rollback();

            return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
        }

        $this->cdnService->batchChangeDnsCnameForLocationSetting($cdn);

        DB::commit();

        return $this->setStatusCode(200)->response('success', null, $cdn);
    }

    /**
     * @param \App\Http\Requests\CdnDeleteRequest $request
     * @param \Hiero7\Models\Domain               $domain
     * @param \Hiero7\Models\Cdn                  $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function destroy(CdnDeleteRequest $request, Domain $domain, Cdn $cdn)
    {
        event(new CdnWasDelete($cdn));

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
