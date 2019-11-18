<?php

namespace App\Http\Controllers\Api\v1;

use Hiero7\Services\ProcessService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessRequest as Request;


class ProcessController extends Controller
{
    
    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
    }

    public function index(Request $request)
    {
        $result = $this->processService->index($request->all(), $this->getUgid($request));

        return $this->response('', null, $result);
    }

    public function getBatchResult(Request $request)
    {
        $result = $this->processService->getBatchResult($request->all(), $this->getUgid($request));

        return $this->response('', null, $result);

    }
}
