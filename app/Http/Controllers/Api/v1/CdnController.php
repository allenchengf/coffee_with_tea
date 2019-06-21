<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\CdnWasCreated;
use App\Events\CdnWasDelete;
use App\Events\CdnWasEdited;
use App\Http\Controllers\Controller;
use App\Http\Requests\CdnCreateRequest;
use App\Http\Requests\CdnUpdateRequest;
use App\Http\Requests\DeleteCdnRequest;
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
        $error = !$request->default ? true :
        $this->cdnService->changeDefaultToTrue($domain, $cdn, $request->edited_by);

        return $this->setStatusCode($error ? 200 : 409)
            ->response(
                $error ? 'success' : 'please contact the admin',
                $error ? null : InternalError::INTERNAL_ERROR,
                $error ? $cdn : []
            );
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
        $this->cdnService->setEditedByOfRequest($request, $this->getJWTPayload()['uuid']);
        $data = $request->only(['edited_by', 'cname']);

        DB::beginTransaction();

        $cdn->update($data);

        if ($cdn->default) {
            if (!event(new CdnWasEdited($domain, $cdn))) {
                DB::rollback();

                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }
        }
        DB::commit();

        return $this->setStatusCode(200)->response('success', null, $cdn);
    }

    /**
     * @param \App\Http\Requests\DeleteCdnRequest $request
     * @param \Hiero7\Models\Domain               $domain
     * @param \Hiero7\Models\Cdn                  $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function destroy(DeleteCdnRequest $request, Domain $domain, Cdn $cdn)
    {
        $deleteDnsPodRecord = event(new CdnWasDelete($cdn));
        $cdn->delete();
        return $this->setStatusCode(200)->response('success', null, []);
    }
}
