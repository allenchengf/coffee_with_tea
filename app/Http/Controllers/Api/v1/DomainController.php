<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Enums\PermissionError;
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

    public function editDomian(Request $request, $domain_id)
    {
        $domain = $this->domainService->getDomainbyId($domain_id);

        $errorCode = null;
        $payload = $this->getJWTPayload();

        $catEdit = (($payload['user_group_id'] == 1) || ($payload['user_group_id'] == $domain->user_group_id));
        $checkDomain = $this->domainService->checkDomainName($request->get('name',''));
        $checkCname = $this->domainService->checkCname($request->get('cname',''));

        if ($catEdit && !$checkDomain && !$checkCname) {
            $domain->name = $request->get('name') ?? $domain->name;
            $domain->cname = $request->get('cname') ?? $domain->cname;
            $domain->save();
        } else {
            $errorCode = !$catEdit ? PermissionError::YOU_DONT_HAVE_PERMISSION:$checkDomain;
            $errorCode = $errorCode ?? $checkCname;
            $domain = [];
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode,
            $domain
        );
    }

    public function destroy(int $domain_id)
    {
        $domain = $this->domainService->getDomainbyId($domain_id);

        $payload = $this->getJWTPayload();
        $errorCode = null;

        $catEdit = (($payload['user_group_id'] == 1) || ($payload['user_group_id'] == $domain->user_group_id));
        if ($catEdit){
            $domain->delete();
        }else{
            $errorCode = PermissionError::YOU_DONT_HAVE_PERMISSION;
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode
        );
    }

}
