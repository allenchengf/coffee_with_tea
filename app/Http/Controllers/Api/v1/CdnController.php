<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\CdnWasCreated;
use App\Events\CdnWasDelete;
use App\Events\CdnWasEdited;
use App\Http\Controllers\Controller;
use App\Http\Requests\CdnRequest;
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
     * @param \App\Http\Requests\CdnRequest $request
     * @param \Hiero7\Models\Domain         $domain
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function store(CdnRequest $request, Domain $domain)
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

            $cdn->update(['dns_provider_id' => $createdDnsProviderRecordResult[0]['data']['record']['id']]);
        }

        DB::commit();

        return $this->setStatusCode(200)->response('success', null, $cdn);

    }

    /**
     * @param \App\Http\Requests\CdnRequest $request
     * @param \Hiero7\Models\Domain         $domain
     * @param \Hiero7\Models\Cdn            $cdn
     *
     * @return \App\Http\Controllers\Api\v1\CdnController
     */
    public function update(CdnRequest $request, Domain $domain, Cdn $cdn)
    {
        $data = $this->cdnService->formatRequest($request, $this->getJWTPayload()['uuid']);

        DB::beginTransaction();

        if (!$this->cdnService->checkCurrentCdnIsDefault($domain, $cdn) and $request->get('default')) {

            $getDefaultRecord = $this->cdnService->getDefaultRecord($domain);

            $getDefaultRecord->update(['default' => false]);

            $domain->getCdnById($cdn->id)->update(['dns_provider_id' => $getDefaultRecord->dns_provider_id]);
        }

        $updateResult = $domain->getCdnById($cdn->id)->update($data);

        if ($updateResult and $data['default']) {

            $cdn = $domain->getCdnById($cdn->id)->first();

            $editedDnsProviderRecordResult = event(new CdnWasEdited($domain, $cdn));

            if (!is_null($editedDnsProviderRecordResult[0]['errorCode']) or array_key_exists('errors',
                $editedDnsProviderRecordResult[0])) {

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
        $defaultCdn = $this->cdnService->getDefaultRecord($domain);
        DB::beginTransaction();

        if ($cdn = $domain->getCdnById($cdn->id)->first()) {
            $changeDnsProviderRecordResult = event(new CdnWasDelete($defaultCdn, $cdn));

            if (!is_null($changeDnsProviderRecordResult[0]['errorCode']) or array_key_exists('errors',
                $changeDnsProviderRecordResult[0])) {
                DB::rollback();

                return $this->setStatusCode(409)->response('please contact the admin', InternalError::INTERNAL_ERROR, []);
            }

            $cdn->delete();
        }

        DB::commit();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
