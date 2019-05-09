<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CdnRequest;
use Hiero7\Models\Cdn;
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
        $result = $domain->cdns()->get();

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    public function store(CdnRequest $request, Domain $domain)
    {
        $data = $request->only(['name', 'cname', 'ttl']);

        $data['edited_by'] = '1'; //just a temporary thing

        $result = $domain->cdns()->create($data);

        return $this->setStatusCode(200)->response('success', null, $result);
    }

    public function update(CdnRequest $request, Domain $domain, $cdn)
    {
        $data = $request->only(['name', 'cname', 'ttl']);

        $data['edited_by'] = '1'; //just a temporary thing

        $domain->cdns()->where('id', $cdn)->update($data);

        return $this->setStatusCode(200)->response('success', null, []);

    }


    public function destroy(Domain $domain, $cdn)
    {
        $domain->cdns()->where('id',$cdn)->delete();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
