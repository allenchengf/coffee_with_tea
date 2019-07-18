<?php

namespace Hiero7\Services;

use DB;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Services\DnsProviderService;
use Illuminate\Database\Eloquent\Collection;

class DnsPodRecordSyncService
{
    protected $dnsProviderService, $domainRepository, $domainName, $cdnProvider, $cdns;

    protected $record = [], $createData = [], $deleteData = [];

    public function __construct(DnsProviderService $dnsProviderService, DomainRepository $domainRepository)
    {
        $this->dnsProviderService = $dnsProviderService;

        $this->domainRepository = $domainRepository;
    }

    /**
     * 取得 DB 與 Dns Pod Different Record
     *
     * @param Domain $domain
     * @return array
     */
    public function getDifferentRecords(Domain $domain = null)
    {
        $record = $domain ? $this->getDomain($domain) : $this->getAllDomain();

        $domainCname = $domain ? $domain->cname : '';

        return $this->getDifferent($record, $domainCname);
    }

    /**
     * Sync DB 與 Dns Pod Record
     *
     * @param Domain $domain
     * @return array
     */
    public function syncAndCheckRecords(Domain $domain = null)
    {
        $differentRecord = $this->getDifferentRecords($domain);

        if (isset($differentRecord['create'])) {
            $this->syncRecord($differentRecord['create'], $differentRecord['different'], $differentRecord['delele']);
        }

        return $this->getDifferentRecords($domain);
    }

    /**
     * Get Different Record
     *
     * @param array $record
     * @param string $domainCname
     * @return array
     */
    private function getDifferent(array $record = [], string $domainCname = '')
    {
        $data = [
            'records' => json_encode($record),
            'sub_domain' => $domainCname,
        ];

        $response = $this->dnsProviderService->getDiffRecord($data);

        if ($this->dnsProviderService->checkAPIOutput($response)) {

            $this->syncDnsProviderRecordId($record, $response['data']['match']);

            return $response['data'];
        }

        return ['error' => true];
    }

    /**
     * Sync Record
     *
     * @param array $createData
     * @param array $diffData
     * @param array $deleteData
     * @return array
     */
    private function syncRecord(array $createData, array $diffData, array $deleteData)
    {
        $data = [
            'create' => json_encode($createData),
            'different' => json_encode($diffData),
            'delele' => json_encode($deleteData),
        ];

        return $this->dnsProviderService->syncRecordToDnsPod($data);
    }

    /**
     * Get Domain All Record
     *
     * @return array $this->record
     */
    public function getAllDomain()
    {
        $domainAll = $this->domainRepository->getAll();

        foreach ($domainAll as $domain) {
            $this->getDomain($domain);
        }

        return $this->record;
    }

    /**
     * Get One Domain Record
     *
     * @param Domain $domain
     * @return array $this->record
     */
    public function getDomain(Domain $domain)
    {
        $this->domainName = $domain->cname;

        app()->call([$this, 'getCdnProvider'], ['ugid' => $domain->user_group_id]);
        $this->cdns = $domain->cdns->keyBy('id');

        $this->getDefaultCdn($domain->cdns()->default()->first());

        $this->getIRouteSetting($domain->locationDnsSettings);

        return $this->record;
    }

    public function getCdnProvider(CdnProvider $cdnProvider, int $ugid)
    {
        $this->cdnProvider = $cdnProvider->where('user_group_id', $ugid)
            ->get()
            ->keyBy('id');

        return $this->cdnProvider;
    }

    /**
     * Get Default Record
     *
     * @param cdn $cdns
     * @return array
     */
    private function getDefaultCdn(Cdn $cdnDefault = null)
    {
        $record = [];

        if ($cdnDefault) {
            $record = [
                'id' => (int) $cdnDefault->provider_record_id,
                'ttl' => (int) $this->cdnProvider[$cdnDefault->cdn_provider_id]['ttl'],
                'value' => $cdnDefault->cname,
                'enabled' => (bool) $this->cdnProvider[$cdnDefault->cdn_provider_id]['status'],
                'name' => $this->domainName,
                'line' => "默认",
                'type' => "CNAME",
            ];

            $this->record[] = $record;
        }

        return $record;
    }

    /**
     * Get Location DNS Setting To Record Data
     *
     * @param array $locationDnsSettings
     * @return array
     */
    private function getIRouteSetting(Collection $locationDnsSettings = null)
    {
        $record = [];

        $locationDnsSettings->map(function ($value, $key) use (&$record) {

            $cdn = $this->cdns[$value->cdn_id];

            $line = $value->location()->first()->network()->first()->name;

            $data = [
                'id' => (int) $value->provider_record_id,
                'ttl' => (int) $this->cdnProvider[$cdn['cdn_provider_id']]['ttl'],
                'value' => $cdn['cname'],
                'enabled' => (bool) $this->cdnProvider[$cdn['cdn_provider_id']]['status'],
                'name' => $this->domainName,
                'line' => $line,
                'type' => "CNAME",
            ];

            $record[] = $data;

        });

        $this->record = array_merge($this->record, $record);

        return $record;
    }

    private function syncDnsProviderRecordId(array $soruceRecord = [], array $matchData = [])
    {
        $soruceRecord = $this->transferRecord($soruceRecord)->keyBy('hash');

        $matchData = $this->transferRecord($matchData)->keyBy('hash');

        $matchData->map(function ($value, $key) use (&$soruceRecord) {
            if (!isset($soruceRecord[$key])) {
                return;
            }

            if ($soruceRecord[$key]['id'] != $value['id']) {
                app()->call([$this, 'updateProviderRecordId'],
                    [
                        'record' => $soruceRecord[$key],
                        'dnsRecordId' => $value['id'],
                    ]);
            }
        });
    }

    /**
     * 比對後需要更新 DB Provider Record ID
     *
     * @param Cdn $cdn
     * @param LocationDnsSetting $locationDnsSetting
     * @param array $record
     * @param array $dnsResponse
     * @return void
     */
    public function updateProviderRecordId(Cdn $cdn, LocationDnsSetting $locationDnsSetting, array $record, int $dnsRecordId)
    {
        if ($record["line"] == "默认") {
            $cdn->where('provider_record_id', $record['id'])
                ->update(['provider_record_id' => $dnsRecordId]);
        } else {
            $locationDnsSetting->where('provider_record_id', $record['id'])
                ->first()
                ->update(['provider_record_id' => $dnsRecordId]);
        }
    }

    private function transferRecord(array $record)
    {
        return collect($record)->map(function ($item, $key) {
            return array_merge($item,
                [
                    'hash' => $this->hashRecord($item),
                ]
            );
        });
    }

    private function hashRecord(array $data)
    {
        $data = collect($data)->only('ttl', 'value', 'enabled', 'name', 'line')->toArray();

        return sha1(json_encode($data));
    }
}