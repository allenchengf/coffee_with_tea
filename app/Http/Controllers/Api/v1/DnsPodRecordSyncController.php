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
use Hiero7\Services\DnsPodRecordSyncService;

class DnsPodRecordSyncController extends Controller
{
    protected $dnsProviderService, $domainService, $domainName, $cdnProvider, $cdns;

    protected $record = [], $matchData = [], $diffData = [], $createData = [], $deleteData = [];

    protected $dnsPodRecordSyncService;

    public function __construct(DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
    }

    public function index()
    {
        $record = $this->dnsPodRecordSyncService->getAllDomain();

        return $this->response('', null, $record);
    }

    public function getDomain(Domain $domain)
    {
        $record = $this->dnsPodRecordSyncService->getDomain($domain);

        return $this->response('', null, $record);
    }

    public function checkDataDiff(Request $request, Domain $domain)
    {
        if ($name = $request->get('name', null)) {

            $domain = $domain->where('name', $name)->first();

            $this->dnsPodRecordSyncService->getDomain($domain);

        } else {
            
            $this->dnsPodRecordSyncService->getDomain($getAllDomain);
        }

        $record = $this->dnsPodRecordSyncService->getDiffRecord();

        return $this->response('', null, $record);
    }

    public function getMatchData($dbRecord, $podRecord)
    {
        $matchData = $dbRecord->diffKeys($this->diffData);
        
        $podMatchData = $podRecord->diffKeys($this->diffData);

        $record = [];

        foreach ($matchData as $key => $value) {

            if( $value['id'] != $podMatchData[$key]['id']){
                app()->call([$this, 'updateProviderRecordId'],
                [
                    'record' => $value,
                    'dnsRecordId' => $podMatchData[$key]['id'],
                ]);
                
                $value['id'] = $podMatchData[$key]['id'];
            }

            $record[] = $value;

        }

        $this->matchData = collect($record)->keyBy('hash');
        
        return $this->matchData;
    }

    /**
     * 比對後需要 新增在 Pod 上面資料
     *
     * @param array $podRecord
     */
    public function getCreateAndDeleteDnsPodData($podRecord)
    {
        $dbRecord = collect($this->record)->keyBy('id');

        $podRecord = $podRecord->values()->keyBy('id');

        $this->createData = $dbRecord->diffKeys($podRecord)->keyBy('hash');
        
        $this->deleteData = $podRecord->diffKeys($dbRecord)->keyBy('hash');
    }

    public function syncDnsData(Request $request, Domain $domain)
    {
        $this->checkDataDiff($request, $domain);

        foreach ($this->deleteData as $record) {
            $this->dnsProviderService->deleteRecord([
                'record_id' => $record['id'],
            ]);
        }

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
                        'dnsRecordId' => $response['data']['record']['id'],
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
        unset($data['id']);
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
        $length = 3000;
        $round = 1;
        $offset = 0;

        for ($i=1; $i <= $round; $i++) { 
            
            $search = [
                'record_type' => 'CNAME',
                'sub_domain' => $domain,
                'length' => $length,
                'offset' => $offset,
            ];

            $response = $this->dnsProviderService->getRecords($search);

            if ($this->dnsProviderService->checkAPIOutput($response)) {
                
                $round = ceil($response['data']['info']['record_total'] / $length); //計算要幾次迴圈
                
                $offset = $length*$i; //計算偏移量

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
        }

        return $record;
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
    public function updateProviderRecordId(Cdn $cdn, LocationDnsSetting $locationDnsSetting, $record, $dnsRecordId)
    {
        if ($record["line"] == "默认") {
            $cdn->where('provider_record_id', $record['id'])
                ->update(['provider_record_id' => $dnsRecordId]);

        } else {
            $locationDnsSetting->where('provider_record_id', $record['id'])
                ->update(['provider_record_id' => $dnsRecordId]);
        }
    }
}
