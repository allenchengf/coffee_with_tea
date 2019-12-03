<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Services\OperationLogService;
use Hiero7\Traits\OperationLogTrait;

class OperationLogController extends Controller
{
    use OperationLogTrait;
    protected $operationLogService;

    /**
     * OperationLogController constructor.
     * @param $operationLogService
     */
    public function __construct(OperationLogService $operationLogService)
    {
        $this->operationLogService = $operationLogService;
    }

    public function index()
    {
        $data = $this->operationLogService->get();

        return $this->response('', null, $data);
    }

    public function show($category)
    {
        $data = $this->operationLogService->show($category);

        return $this->response('', null, $data);
    }

    public function categoryList()
    {
        $categoryList = collect(config('logging.category'))->values();
        return $this->response('', null, $categoryList);
    }
}
