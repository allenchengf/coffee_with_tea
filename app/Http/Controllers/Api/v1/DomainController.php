<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Services\DomainService;

class DomainController extends Controller
{

    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $data['uuid'] = $this->getJWTPayload()['uuid'];
        
        extract($this->domainService->create($data));

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            $domain
        );
    }

}
