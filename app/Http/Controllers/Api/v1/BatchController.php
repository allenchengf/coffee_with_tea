<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Hiero7\Services\BatchService;
use App\Http\Requests\BatchRequest;

class BatchController extends Controller
{
    protected $batchService;

    public function __construct(BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    public function store(BatchRequest $request){
        
        config(['database.connections.mysql.options' => [
            \PDO::ATTR_PERSISTENT => true
        ]]);

        DB::purge('mysql');
        DB::reconnect('mysql');        
        DB::connection()->disableQueryLog();
        $errors = $this->batchService->store($request->domains, $request->get('user'));

        DB::connection()->enableQueryLog();
        return $this->response('Success', null, $errors);
    }
}
