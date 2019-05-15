<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Services\ContinentService;
use Illuminate\Http\Request;

class ContinentController extends Controller
{
    protected $continentService;

    /**
     * ContinentController constructor.
     */
    public function __construct(ContinentService $continentService)
    {
        $this->continentService = $continentService;
    }

    public function index()
    {
        $data = $this->continentService->getAll();
        return $this->response("Success", null, $data);
    }
}
