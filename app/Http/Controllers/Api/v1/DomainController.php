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

    /**
     * Get Domain By ID
     *
     * @param Domain $domain
     */
    public function getDomainById(Domain $domain)
    {
        $domain->cdns;
        $domain->domainGroup;
        $domain->toArray;
        $dnsPodDomain = env('DNS_POD_DOMAIN');
        return $this->response('', null, compact('domain', 'dnsPodDomain'));
    }

    /**
     * Get Domain function
     *
     * $request->user_group_id，預設為 login user_group_id (可選)
     * $request->without_group (可選)
     *
     * 如果 login user_group_id == 1 && $request->user_group_id == null，則會得到 All Domain
     * @param Request $request
     * @param Domain $domain
     */
    public function getDomain(Request $request, Domain $domain)
    {
        $user_group_id = $this->getUgid($request);

        $domains = !$request->has('user_group_id') && $user_group_id == 1 ?
        $domain->with('cdns', 'domainGroup')->get() :
        $domain->with('cdns', 'domainGroup')->where(compact('user_group_id'))->get();
        $domains->toArray();

        if($request->has('without_group')){
            $domains = $domains->filter(function ($item) {

                return $item->domainGroup->isEmpty();
            });
            $domains = $domains->values();
        }
        $dnsPodDomain = env('DNS_POD_DOMAIN');

        return $this->response('', null, compact('domains', 'dnsPodDomain'));
    }

    public function create(Request $request, Domain $domain)
    {
        $ugid = $this->getUgid($request);
        $request->merge([
            'user_group_id' => $ugid,
            'cname' => $this->domainService->cnameFormat($request, $ugid),
        ]);

        if (!$errorCode = $this->domainService->checkUniqueCname($request->cname)) {
            $domain = $domain->create($request->all());
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode ? $errorCode : null,
            $errorCode ? [] : $domain
        );

    }

    public function editDomain(Request $request, Domain $domain)
    {
        $domain->update($request->only('name', 'label', 'edited_by'));
        $domain->cdns;
        return $this->response('', null, $domain);
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return $this->response();
    }
}
