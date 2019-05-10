<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CdnRequest;
use Hiero7\Models\Domain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CdnController extends Controller
{

    public function index(Domain $domain)
    {
        $result = $domain->cdns()->orderBy('created_at', 'asc')->get();

        return $this->setStatusCode($result ? 200 : 404)->response('success', null, $result);
    }

    public function store(CdnRequest $request, Domain $domain)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);

        if ( ! $domain->cdns()->exists()) {
            $request->merge(['default' => true]);
        }

        $domain->cdns()->create($request->only(['name', 'cname', 'ttl', 'edited_by', 'default']));

        return $this->setStatusCode(200)->response('success', null, []);
    }

    public function update(CdnRequest $request, Domain $domain, $cdn)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);

        $data = $request->only(['name', 'cname', 'ttl', 'edited_by', 'default']);

        if ($getDefaultRecord = $domain->cdns()->where('default',
                true)->first() and $getDefaultRecord->id != $cdn
                                   and $request->get('default') == true) {

            $data['default'] = false;
        }

        $domain->cdns()->getById($cdn)->update($data);

        return $this->setStatusCode(200)->response('success', null, []);

    }


    public function destroy(Domain $domain, $cdn)
    {
        $domain->cdns()->getById($cdn)->delete();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
