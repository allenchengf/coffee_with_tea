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
        return $this->operationLogService->get();
    }

    public function show($category)
    {
        return $this->operationLogService->show($category);
    }

    public function categoryList()
    {
        $categoryList = collect(config('logging.category'))->values();
        return $this->response('', null, $categoryList);
    }
}
