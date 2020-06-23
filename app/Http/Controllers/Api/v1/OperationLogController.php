<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimestampRequest;
use Carbon\Carbon;
use Hiero7\Models\ChangeLogForPortal;
use Hiero7\Repositories\ChangeLogForPortalRepository;
use Hiero7\Services\OperationLogService;
use Hiero7\Traits\OperationLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class OperationLogController extends Controller
{
    use OperationLogTrait;

    protected $operationLogService;

    /**
     * OperationLogController constructor.
     * @param $operationLogService
     */
    public function __construct(
        OperationLogService $operationLogService,
        ChangeLogForPortalRepository $changeLogForPortalRepository
    ) {
        $this->operationLogService = $operationLogService;

        $this->changeLogForPortalRepository = $changeLogForPortalRepository;
    }

    public function index()
    {
        $data = $this->operationLogService->get();

        return $this->response('', null, $data);
    }

    public function show(Request $request, $category)
    {
        $page = $request->get('current_page', 1);

        $pageCount = $request->get('per_page', 3000);

        $data = $this->operationLogService->show($category, $page, $pageCount);

        return $this->response('', null, $data);
    }

    public function categoryList()
    {
        $categoryList = collect(config('logging.category'))->values();
        return $this->response('', null, $categoryList);
    }

    public function getForPortalLog(TimestampRequest $request)
    {
        $startTime = $request->get('start_time');
        $endTime   = $request->get('end_time');

        $changeLog = $this->changeLogForPortalRepository->getLogByTime($startTime, $endTime);

        return $this->response('', null, $changeLog);
    }
}
