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

    public function formatRequest(CdnRequest $request, $uuid)
    {
        $this->setEditedByOfRequest($request, $uuid);

        return $request->only([
            'cdn_provider_id',
            'cname',
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