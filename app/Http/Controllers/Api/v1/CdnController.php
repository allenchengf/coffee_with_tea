<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\CdnRequest;
use Hiero7\Models\Cdn;
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

    public function update(CdnRequest $request, Domain $domain, Cdn $cdn)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);

        $data = $request->only(['name', 'cname', 'ttl', 'edited_by', 'default']);

        $getDefaultRecord = $domain->cdns()->default()->first();

        if ($getDefaultRecord and $getDefaultRecord->id != $cdn->id and $request->get('default')) {

            $data['default'] = false;
        }

        $domain->cdns()->getById($cdn->id)->update($data);

        return $this->setStatusCode(200)->response('success', null, []);

    }


    public function destroy(Domain $domain, Cdn $cdn)
    {
        $domain->cdns()->getById($cdn->id)->delete();

        return $this->setStatusCode(200)->response('success', null, []);
    }
}
