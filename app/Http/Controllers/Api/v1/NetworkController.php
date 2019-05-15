<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Hiero7\Services\NetworkService;
use App\Http\Requests\NetworkRequest as Request;

class NetworkController extends Controller
{
    protected $networkService;

    /**
     * NetworkController constructor.
     */
    public function __construct(NetworkService $networkService)
    {
        $this->networkService = $networkService;
    }

    public function index()
    {
        $data = $this->networkService->getAll();
        return $this->response("Success", null, $data);
    }
}
