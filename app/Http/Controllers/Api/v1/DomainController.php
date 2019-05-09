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

    public function getAllDomain()
    {
        $domain = $this->domainService->getAllDomain();

        return $this->response('', null, $domain);
    }

    public function getDomain(int $ugid)
    {
        $domain = $this->domainService->getDomain($ugid);

        return $this->response('', null, $domain);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $data['cname'] = $request->get('cname') ?? $request->get('name');
        $data['edited_by'] = $this->getJWTPayload()['uuid'];

        extract($this->domainService->create($data));

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            $domain
        );
    }

}
