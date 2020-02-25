<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DnsPodRecordSyncRequest as Request;
use Hiero7\Models\Domain;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Services\SyncAllRecordService;

class DnsPodRecordSyncController extends Controller
{
    protected $dnsProviderService, $domainService, $domainName, $cdnProvider, $cdns;

    protected $record = [], $matchData = [], $diffData = [], $createData = [], $deleteData = [];

    protected $dnsPodRecordSyncService;

    public function __construct(DnsPodRecordSyncService $dnsPodRecordSyncService, SyncAllRecordService $syncAllRecordService)
    {
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;

        $this->syncAllRecordService = $syncAllRecordService;
    }

    public function index()
    {
        $records = $this->syncAllRecordService->getAllRecord();

        return $this->response('', null, $records);
    }

    public function getDomain(Domain $domain)
    {
        $record = $this->dnsPodRecordSyncService->getDomainRecord($domain);

        return $this->response('', null, $record);
    }

    public function checkDataDiff(Request $request, Domain $domain)
    {
        $domain = $this->getDomainObject($request, $domain);

        if (!$domain) {
            $localRecords = $this->syncAllRecordService->getAllRecord();

            $records = $this->syncAllRecordService->getDifferent($localRecords);
        } else {

            $records = $this->dnsPodRecordSyncService->getDifferentRecords($domain);
        }

        return $this->response('', null, $records);
    }

    public function syncDnsData(Request $request, Domain $domain)
    {
        $domain = $this->getDomainObject($request, $domain);

        if (!$domain) {
            $localRecords = $this->syncAllRecordService->getAllRecord();

            $checkRecords = $this->syncAllRecordService->getDifferent($localRecords);

            $this->syncAllRecordService->syncRecords($checkRecords);

            $records = $this->syncAllRecordService->getDifferent($localRecords);

        } else {
            $records = $this->dnsPodRecordSyncService->syncAndCheckRecords($domain);
        }

        return $this->response('', null, $records);
    }

    public function syncDnsDataByDomain(Domain $domain)
    {
        $records = $this->dnsPodRecordSyncService->syncAndCheckRecords($domain);

        return $this->response('', null, $records);
    }

    private function getDomainObject(Request $request, Domain $domain)
    {
        $name = $request->get('name');

        $domain = $request->has('name') ?
        $domain->where('name', $name)->first() : null;

        return $domain;
    }
}
