<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Hiero7\Services\ProcessService;
use App\Http\Controllers\Controller;


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
}
