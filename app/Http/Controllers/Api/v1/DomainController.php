<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;

class DomainController extends Controller
{

    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    public function getDomain(Request $request)
    {
        $ugid = $this->getUgid($request);
        $domain = $this->domainService->getDomain($ugid)->toArray();
        $dnsPodDomain = env('DNS_POD_DOMAIN');
        return $this->response('', null, compact('domain', 'dnsPodDomain'));
    }

    public function create(Request $request, Domain $domain)
    {
        $request->merge([
            'edited_by' => $this->getJWTPayload()['uuid'],
            'user_group_id' => $this->getUgid($request),
            'cname' => $request->get('cname') ?? $request->get('name'),
        ]);
        $data = $request->all();

        $errorCode = $this->domainService->checkDomainAndCnameUnique($data);

        if (!$errorCode) {
            $domainInfo = $domain->create($data);
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            $domainInfo ?? []
        );
    }

    public function editDomian(Request $request, Domain $domain)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $errorCode = null;

        $checkDomain = $this->domainService->checkDomainName($request->get('name', ''), $domain->id);
        $checkCname = $this->domainService->checkCname($request->get('cname', ''), $domain->id);

        if (!$checkDomain && !$checkCname) {
            $domain->update($request->only('name', 'cname', 'edited_by'));
        } else {
            $errorCode = $checkDomain ?? $checkCname;
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            $errorCode ? [] : $domain
        );
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return $this->response();
    }

    private function getUgid($request)
    {
        $getPayload = $this->getJWTPayload();

        $ugid = (($getPayload['user_group_id'] == $request->get('user_group_id')) ||
            ($getPayload['user_group_id'] == 1)) ?
        $request->get('user_group_id', $getPayload['user_group_id']) : $getPayload['user_group_id'];

        return $ugid;
    }
}
