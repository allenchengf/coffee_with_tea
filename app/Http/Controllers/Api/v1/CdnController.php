<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CdnRequest;
use Hiero7\Models\Domain;
use Illuminate\Http\Request;
use Hiero7\Repositories\CdnRepository;
use App\Http\Controllers\Controller;

class CdnController extends Controller
{
    protected $cdnRepository;

    /**
     * CdnController constructor.
     *
     * @param $cdnRepository
     */
    public function __construct(CdnRepository $cdnRepository)
    {
        $this->cdnRepository = $cdnRepository;
    }

    public function index(Domain $domain)
    {
        $result = $domain->cdns()->orderBy('created_at', 'asc')->get();

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    public function store(CdnRequest $request, Domain $domain)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);

        $domain->cdns()->create($request->only(['name', 'cname', 'ttl', 'edited_by']));

        return $this->setStatusCode(200)->response('success', null, []);
    }

    public function update(CdnRequest $request, Domain $domain, $cdn)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);

        $domain->cdns()->where('id', $cdn)->update($request->only(['name', 'cname', 'ttl', 'edited_by']));

        return $this->setStatusCode(200)->response('success', null, []);

    }


    public function destroy(Domain $domain, $cdn)
    {
        $domain->cdns()->where('id', $cdn)->delete();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
