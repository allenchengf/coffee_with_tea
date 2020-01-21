<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Hiero7\Services\{BatchService,BatchGroupService};
use App\Http\Requests\BatchRequest;
use Hiero7\Models\DomainGroup;


class BatchController extends Controller
{
    protected $batchService;
    protected $batchGroupService;

    public function __construct(BatchService $batchService,BatchGroupService $batchGroupService)
    {
        $this->batchService = $batchService;
        $this->batchGroupService = $batchGroupService;
        config(['database.connections.mysql.options' => [
            \PDO::ATTR_PERSISTENT => true
        ]]);
        DB::purge(env('DB_CONNECTION'));
        DB::reconnect(env('DB_CONNECTION'));        
        DB::connection()->disableQueryLog();

    }

    /**
     * 原本的 Batch，Justin 建議保留以供測試。
     *
     * @return void
     */
    public function oldStore(BatchRequest $request)
    {
        $errors = $this->batchService->store($request->domains, $request->get('user'));
        return $this->response('Success', null, $errors);
    }
    
    /**
     * 有在使用的
     *
     * @param BatchRequest $request
     * @return void
     */
    public function store(BatchRequest $request)
    {        
        $errors = $this->batchService->process($request->domains, $request->get('user'),$this->getUgid($request));

        DB::connection()->enableQueryLog();
        return $this->response('Success', null, $errors);
    }

    public function storeDomainToGroup(BatchRequest $request,DomainGroup $domainGroup)
    {
        $errors = $this->batchGroupService->store($request->domains, $domainGroup, $request->get('user'));

        DB::connection()->enableQueryLog();
        return $this->response('Success', null, $errors);
    }
}
