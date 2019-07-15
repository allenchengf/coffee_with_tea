<?php


namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use Hiero7\Services\OperationLogService;
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        return $this->operationLogService->get($request);
    }
}
