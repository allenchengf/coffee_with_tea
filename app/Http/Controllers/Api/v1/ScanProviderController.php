<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanProviderRequest;
use Hiero7\Models\Domain;

class ScanProviderController extends Controller
{

    public function index()
    {
        $scanProvider = collect(config('scanProvider'))->keys();

        return $this->response("", null, $scanProvider);
    }

}
