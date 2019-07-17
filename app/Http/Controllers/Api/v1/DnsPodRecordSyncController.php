<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DnsPodRecordSyncRequest as Request;
use Hiero7\Models\Domain;
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
        $record = $this->getDomainRecord($request, $domain);

        return $this->response('', null, $record);
    }

    public function syncDnsData(Request $request, Domain $domain)
    {

        $diffRecord = $this->getDomainRecord($request, $domain);

        $data = $this->dnsPodRecordSyncService->syncRecord($diffRecord['create'], $diffRecord['diff'], $diffRecord['delele']);

        $record = $this->getDomainRecord($request, $domain);

        return $this->response('', null, $record);
    }

    private function getDomainRecord(Request $request, Domain $domain)
    {
        $domain = $request->has('name') ?
        $domain->where('name', $request->get('name'))->first() : null;

        return $this->dnsPodRecordSyncService->getDiffRecords($domain);
    }
}
