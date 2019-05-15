<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-14
 * Time: 11:18
 */

namespace Hiero7\Services;

use App\Http\Requests\CdnRequest;
use Hiero7\Models\Domain;
use Hiero7\Models\Cdn;

class CdnService
{
    public function setEditedByOfRequest(CdnRequest $request, $uuid)
    {
        $request->merge(['edited_by' => $uuid]);
    }

    public function setTTLValueOfRequest(CdnRequest $request)
    {
        if ($request->method() == 'PUT') {
            return;
        }

        $request->merge(['ttl' => env('CDN_TTL')]);
    }

    public function formatRequest(CdnRequest $request, $uuid)
    {
        $request->has('ttl') ? $request->get('ttl') : $this->setTTLValueOfRequest($request);

        $this->setEditedByOfRequest($request, $uuid);

        return $request->only([
            'name',
            'cname',
            'ttl',
            'edited_by',
            'default'
        ]);

    }

    public function getDefaultRecord(Domain $domain)
    {
        return $domain->cdns()->default()->first();
    }

    public function checkCurrentCdnIsDefault(Domain $domain, Cdn $cdn)
    {
        $getDefaultRecord = $this->getDefaultRecord($domain);

        if ($getDefaultRecord and $getDefaultRecord->id == $cdn->id) {

            return true;
        }

        return false;
    }
}