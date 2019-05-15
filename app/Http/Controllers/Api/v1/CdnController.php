<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CdnRequest;
use App\Http\Requests\DeleteCdnRequest;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Services\CdnService;
use Hiero7\Services\DnsProviderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CdnController extends Controller
{
    protected $dnsProviderService;

    protected $cdnService;

    /**
     * CdnController constructor.
     *
     * @param \Hiero7\Services\DnsProviderService $dnsProviderService
     * @param \Hiero7\Services\CdnService         $cdnService
     */
    public function __construct(DnsProviderService $dnsProviderService, CdnService $cdnService)
    {
        $this->dnsProviderService = $dnsProviderService;

        $this->cdnService = $cdnService;
    }

    public function index(Domain $domain)
    {
        $result = $domain->cdns()->orderBy('created_at', 'asc')->get();

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    public function store(CdnRequest $request, Domain $domain)
    {
        if ( ! $domain->cdns()->exists()) {

            $request->merge(['default' => true]);
        }

        if ($cdn = $domain->cdns()->create($this->cdnService->formatRequest($request,
                $this->getJWTPayload()['uuid'])) and $request->input('default')) {

            $dnsProviderRecord = $this->dnsProviderService->createRecord([
                'sub_domain' => $domain->cname,
                'value'      => $cdn->cname,
                'ttl'        => $cdn->ttl,
                'status'     => true
            ]);

            $cdn->update(['dns_provider_id' => $dnsProviderRecord['data']['record']['id']]);
        };

        return $this->setStatusCode(200)->response('success', null, []);
    }

    public function update(CdnRequest $request, Domain $domain, Cdn $cdn)
    {
        $data = $this->cdnService->formatRequest($request, $this->getJWTPayload()['uuid']);

        if ( ! $this->cdnService->checkCurrentCdnIsDefault($domain, $cdn) and $request->get('default')) {

            $getDefaultRecord = $this->cdnService->getDefaultRecord($domain);

            $getDefaultRecord->update(['default' => false]);

            $domain->getCdnById($cdn->id)->update(['dns_provider_id' => $getDefaultRecord->dns_provider_id]);
        }

        if ($domain->getCdnById($cdn->id)->update($data) and $data['default']) {

            $cdn = $domain->getCdnById($cdn->id)->first();

            $result = $this->dnsProviderService->editRecord([
                'record_id'   => $cdn->dns_provider_id,
                'sub_domain'  => $domain->cname,
                'record_type' => "CNAME",
                'record_line' => "默认",
                'value'       => $cdn->cname,
                'ttl'         => $cdn->ttl,
                'status'      => $cdn->default
            ]);
        }

        return $this->setStatusCode(200)->response('success', null, []);

    }


    public function destroy(DeleteCdnRequest $request, Domain $domain, Cdn $cdn)
    {
        if ($cdn = $domain->getCdnById($cdn->id)->first()) {

            $cdn->delete();

        }

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
