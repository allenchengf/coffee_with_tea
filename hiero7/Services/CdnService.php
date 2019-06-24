<?php
/**
 * Created by PhpStorm.
 * User: hanhanhu
 * Date: 2019-05-14
 * Time: 11:18
 */

namespace Hiero7\Services;

use App\Events\CdnWasBatchEdited;
use App\Events\CdnWasEdited;
use DB;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Illuminate\Http\Request;

class CdnService
{
    public function setEditedByOfRequest(Request $request, $uuid)
    {
        $request->merge(['edited_by' => $uuid]);
    }

    public function formatRequest(Request $request, $uuid)
    {
        $this->setEditedByOfRequest($request, $uuid);

        return $request->only([
            'cdn_provider_id',
            'cname',
            'edited_by',
            'default',
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

    /**
     * changeDefaultRecord function
     *
     * @param Domain $domain
     * @param Cdn $cdn 要改變 $cdn->default 的目標
     * @param uuid $edited_by
     * @return boolean
     */
    public function changeDefaultToTrue(Domain $domain, Cdn $cdn, $edited_by = null): bool
    {
        if ($this->checkCurrentCdnIsDefault($domain, $cdn)) {
            return true;
        }

        DB::beginTransaction();

        $getDefaultRecord = $this->getDefaultRecord($domain);
        $getDefaultRecord->update(['default' => false]);

        $cdn->update([
            'provider_record_id' => $getDefaultRecord->provider_record_id,
            'default' => true,
            'edited_by' => $edited_by,
        ]);

        $cdn = $domain->getCdnById($cdn->id)->first();

        if (!event(new CdnWasEdited($domain, $cdn))) {

            DB::rollback();
            return false;
        }

        DB::commit();

        return true;
    }

    /**
     * 修改 Dns Provider CNAME function
     *
     * @param Domain $domain
     * @param Cdn $cdn
     * @return boolean
     */
    public function changeDnsProviderCname(Domain $domain, Cdn $cdn): bool
    {
        if ($cdn->default) {
            if (!event(new CdnWasEdited($domain, $cdn))) {
                return false;
            }
        }
        return true;
    }

    /**
     * 批次修改 Location DNS Setting 同個 $cdn->id 的 CNAME
     *
     * @param Cdn $cdn
     */
    public function batchChangeDnsCnameForLocationSetting(Cdn $cdn)
    {
        $dnsProviderIdArray = $cdn->getlocationDnsSettingDomainId($cdn->id)->toArray();
        return event(new CdnWasBatchEdited($cdn->cname, $dnsProviderIdArray, 'value'));
    }
}
