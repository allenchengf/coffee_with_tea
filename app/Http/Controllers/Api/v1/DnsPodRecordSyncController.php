<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DnsPodRecordSyncRequest as Request;
use DB;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Services\DnsProviderService;

class DnsPodRecordSyncController extends Controller
{
    protected $dnsProviderService, $domainService, $domainName, $cdnProvider, $cdns;
    protected $record = [], $diffData = [], $createData = [];

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
    }

    public function index(Domain $domain)
    {
        $domainAll = $domain->all();

        foreach ($domainAll as $domain) {
            $this->getDomain($domain);
        }

        return $this->response('', null, $this->record);
    }

    public function getDomain(Domain $domain)
    {
        $this->domainName = $domain->cname;

        app()->call([$this, 'getCdnProvider'], ['ugid' => $domain->user_group_id]);

        $domain->locationDnsSettings;

        $this->cdns = $domain->cdns->keyBy('id');

        $this->getDefaultCdn($domain->cdns);

        $this->getIRouteSetting($domain->locationDnsSettings);

        return $this->response('', null, $this->record);
    }

    public function checkDataDiff(Request $request, Domain $domain)
    {
        $this->diffData = $this->createData = collect([]);

        if ($name = $request->get('name', null)) {

            $domain = $domain->where('name', $name)->first();

            $this->getDomain($domain);

            $podRecord = collect($this->getDnsPodRecord($domain->cname))->keyBy('hash');
        } else {

            $this->index($domain);

            $podRecord = collect($this->getDnsPodRecord())->keyBy('hash');
        }

        $dbRecord = collect($this->record)->keyBy('hash');

        $this->needCreateDnsPodData($podRecord);

        $this->diffData = $dbRecord->diffKeys($podRecord);

        $matchData = $dbRecord->diffKeys($this->diffData)->values();

        $this->diffData = $this->diffData->diffKeys($this->createData->keyBy('hash'));

        $data = [
            'diff' => $this->diffData->values(),
            'create' => $this->createData->values(),
            'match' => $matchData,
        ];

        return $this->response('', null, $data);

    }

    public function syncDnsData(Request $request, Domain $domain)
    {
        $this->checkDataDiff($request, $domain);

        foreach ($this->diffData as $record) {
            $this->dnsProviderService->editRecord([
                'sub_domain' => $record['name'],
                'value' => $record['value'],
                'record_id' => $record['id'],
                'record_line' => $record['line'],
                'ttl' => $record['ttl'],
                'status' => $record['enabled'],
            ]);
        }

        foreach ($this->createData as $record) {
            DB::beginTransaction();

            $response = $this->dnsProviderService->createRecord([
                'sub_domain' => $record['name'],
                'value' => $record['value'],
                'record_line' => $record['line'],
                'ttl' => $record['ttl'],
                'status' => $record['enabled'],
            ]);

            if ($this->dnsProviderService->checkAPIOutput($response)) {
                app()->call([$this, 'updateProviderRecordId'],
                    [
                        'record' => $record,
                        'dnsResponse' => $response,
                    ]);

                DB::commit();
                continue;
            }
            DB::rollback();
        }

        return $this->response();
    }

    public function getCdnProvider(CdnProvider $cdnProvider, $ugid)
    {
        $this->cdnProvider = $cdnProvider->where('user_group_id', $ugid)
            ->get()
            ->keyBy('id');

        return $this->cdnProvider;
    }

    private function getDefaultCdn($cdns)
    {
        $cdnDefault = $cdns->first(function ($value, $key) {
            return $value->default == 1;
        });

        $record = [
            'id' => (int) $cdnDefault->provider_record_id,
            'ttl' => (int) $this->cdnProvider[$cdnDefault->cdn_provider_id]['ttl'],
            'value' => $cdnDefault->cname,
            'enabled' => (bool) $this->cdnProvider[$cdnDefault->cdn_provider_id]['status'],
            'name' => $this->domainName,
            'line' => "默认",
        ];

        $record['hash'] = $this->hashRecord($record);

        $this->record[] = $record;

        return $record;
    }

    /**
     * Get Location DNS Setting To Record Data
     *
     * @param array $locationDnsSettings
     * @return array
     */
    private function getIRouteSetting($locationDnsSettings)
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
            ];

            $data['hash'] = $this->hashRecord($data);

            $record[] = $data;

        });

        $this->record = array_merge($this->record, $record);

        return $record;
    }

    private function hashRecord(array $data)
    {
        return sha1(json_encode($data));
    }

    /**
     * 取得 DNS Pod Record 資料
     *
     * @param string $domain
     * @return void
     */
    private function getDnsPodRecord(string $domain = null)
    {
        $record = [];

        $search = [
            'record_type' => 'CNAME',
            'sub_domain' => $domain,
            'length' => 3000,
        ];

        $response = $this->dnsProviderService->getRecords($search);

        if ($this->dnsProviderService->checkAPIOutput($response)) {

            // 將 Response Data 格式統一
            collect($response['data']['records'])->map(function ($value, $key) use (&$record) {

                $data = [
                    'id' => (int) $value['id'],
                    'ttl' => (int) $value['ttl'],
                    'value' => rtrim($value['value'], "."),
                    'enabled' => (bool) $value['enabled'],
                    'name' => $value['name'],
                    'line' => $value['line'],
                ];

                $data['hash'] = $this->hashRecord($data);

                $record[] = $data;

            });
        }

        return $record;
    }

    /**
     * 比對後需要 新增在 Pod 上面資料
     *
     * @param array $podRecord
     * @return array
     */
    public function needCreateDnsPodData($podRecord)
    {
        $dbRecord = collect($this->record)->keyBy('id');

        $podRecord = $podRecord->values()->keyBy('id');

        $this->createData = $dbRecord->diffKeys($podRecord);

        return $this->createData;
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
    public function updateProviderRecordId(Cdn $cdn, LocationDnsSetting $locationDnsSetting, $record, $dnsResponse)
    {
        if ($record["line"] == "默认") {
            $cdn->where('provider_record_id', $record['id'])
                ->update(['provider_record_id' => $dnsResponse['data']['record']['id']]);

        } else {
            $locationDnsSetting->where('provider_record_id', $record['id'])
                ->update(['provider_record_id' => $dnsResponse['data']['record']['id']]);
        }
    }
}
