<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    public function index(Request $request)
    {
        $content = $this->NetWorkService->getDomainList($request->all());
        return $this->dnsPodAPIOutPut($content);
    }
}
