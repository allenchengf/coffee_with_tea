<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Domain;
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
        $domain = $this->domainService->getAllDomain()->toArray();
        $dnsPodDomain = env('DNS_POD_DOMAIN');

        return $this->response('', null, compact('domain', 'dnsPodDomain'));
    }

    public function getDomain(int $ugid)
    {
        $domain = $this->domainService->getDomain($ugid)->toArray();
        $dnsPodDomain = env('DNS_POD_DOMAIN');

        return $this->response('', null, compact('domain', 'dnsPodDomain'));
    }

    public function create(Request $request, Domain $domain)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid'],
            'user_group_id' => $this->getJWTPayload()['user_group_id']]);
        $data = $request->all();
        $data['cname'] = $request->get('cname') ?? $request->get('name');

        $errorCode = $this->domainService->checkDomainAndCnameUnique($data);
        if (!$errorCode) {
            $domainInfo = $domain->create($data);
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            isset($domainInfo) ? $domainInfo : []
        );
    }

    public function editDomian(Request $request, Domain $domain)
    {
        $request->merge(['edited_by' => $this->getJWTPayload()['uuid']]);
        $errorCode = null;

        $checkDomain = $this->domainService->checkDomainName($request->get('name', ''), $domain->id);
        $checkCname = $this->domainService->checkCname($request->get('cname', ''), $domain->id);

        if ($this->checkCanEditDomain($domain) && !$checkDomain && !$checkCname) {

            $domain->update($request->only('name', 'cname', 'edited_by'));

        } else {
            $errorCode = $checkDomain ?? $checkCname;
            $errorCode = $errorCode ?? PermissionError::YOU_DONT_HAVE_PERMISSION;
            $domain = [];
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            isset($domain) ? $domain : []
        );
    }

    public function destroy(Domain $domain)
    {

        $errorCode = null;

        if ($this->checkCanEditDomain($domain)) {
            $domain->delete();
        } else {
            $errorCode = PermissionError::YOU_DONT_HAVE_PERMISSION;
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode
        );
    }

    private function checkCanEditDomain($domain)
    {
        $payload = $this->getJWTPayload();

        return (($payload['user_group_id'] == 1) || ($payload['user_group_id'] == $domain->user_group_id));
    }

}
