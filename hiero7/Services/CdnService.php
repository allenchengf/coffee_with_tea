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
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Services\DnsProviderService;
use Illuminate\Http\Request;

class CdnService
{
    protected $defaultLine;
    protected $dnsProviderService;

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
        $this->defaultLine = "默认";
    }

    public function setEditedByOfRequest(Request $request, $uuid)
    {
        $request->merge(['edited_by' => $uuid]);
    }

    public function formatRequest(Request $request, $uuid)
    {
        $this->setEditedByOfRequest($request, $uuid);

        $this->modifyCNAME($request);

        return $request->only([
            'cdn_provider_id',
            'cname',
            'edited_by',
            'default',
        ]);
    }

    public function modifyCNAME(Request $request): void
    {
        $request->merge([
            'cname' => strtolower($request->get('cname')),
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
     * @param Domain $domain 目標
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

    /**
     * 取得 DNS Pod Record By CDN
     *
     * @param Cdn $cdn
     * @return array
     */
    public function getRecordByCDN(Cdn $cdn)
    {
        $records = $this->getDnsPodDefaultRecord($cdn);
        $cdnProvider = $this->getCDNProviderByCDN($cdn);

        $recordTTL = (int) $cdnProvider->ttl;
        $recordValue = $cdn->cname;
        $recordStatus = (bool) $cdnProvider->status;
        $recordName = $cdn->domain->cname;

        foreach ($cdn['locationDnsSetting'] as $locationDnsSetting) {
            $line = $this->getDNSPodLineByLocationDnsSetting($locationDnsSetting);

            $records[] = [
                'id' => (int) $locationDnsSetting->provider_record_id,
                'ttl' => $recordTTL,
                'value' => $recordValue,
                'enabled' => $recordStatus,
                'name' => $recordName,
                'line' => $line,
                'type' => "CNAME",
            ];
        }

        return $records;
    }

    /**
     * 取得 DNS Pod Record By Default Records
     *
     * @param Cdn $cdn
     * @return array
     */
    public function getDnsPodDefaultRecord(Cdn $cdn): array
    {
        if ($cdn->default) {
            $cdnProvider = $this->getCDNProviderByCDN($cdn);

            // record 的狀態已 cdn provider 的狀態為主
            $enabled = $cdnProvider->status;

            $record = [
                'id' => (int) $cdn->provider_record_id,
                'ttl' => (int) $cdnProvider->ttl,
                'value' => $cdn->cname,
                'enabled' => (bool) $enabled,
                'name' => $cdn->domain->cname,
                'line' => $this->defaultLine,
                'type' => "CNAME",
            ];

            return $record;
        }

        return [];
    }

    /**
     * 取得 CDN Provider By CDN
     *
     * @param Cdn $cdn
     * @return CdnProvider
     */
    public function getCDNProviderByCDN(Cdn $cdn)
    {
        if (!isset($this->cdnProviders[$cdn->cdn_provider_id])) {
            $this->tempCDNProvider($cdn->cdnProvider);
        }

        return $this->cdnProviders[$cdn->cdn_provider_id];
    }

    /**
     * 取得 Line
     *
     * @param Cdn $cdn
     * @return string
     */
    public function getDNSPodLineByLocationDnsSetting(LocationDnsSetting $locationDnsSetting)
    {
        if (!isset($this->lines[$locationDnsSetting->location_networks_id])) {
            $this->tempDNSPodLine($locationDnsSetting);
        }

        return $this->lines[$locationDnsSetting->location_networks_id];
    }

    /**
     * 暫存 CDN Provider Module
     *
     * @param CdnProvider $cdnProvider
     */
    private function tempCDNProvider(CdnProvider $cdnProvider)
    {
        $this->cdnProviders[$cdnProvider->id] = $cdnProvider;
    }

    /**
     * 暫存 Line
     *
     * @param CdnProvider $cdnProvider
     */
    private function tempDNSPodLine(LocationDnsSetting $locationDnsSetting)
    {
        $line = $locationDnsSetting->location()->first()->network()->first()->name;

        $this->lines[$locationDnsSetting->location_networks_id] = $line;
    }

}
