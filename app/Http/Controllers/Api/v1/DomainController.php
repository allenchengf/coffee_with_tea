<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;
use Hiero7\Enums\PermissionError;
use App\Events\CdnWasDelete;


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
     * $request->domain_group_id，預設為 all (可選) 
     *
     * 如果 login user_group_id == 1 && $request->user_group_id == null，則會得到 All Domain
     * 如果 domain_group_id == 0 會得到沒有 Group 的 Domain
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

        if($request->has('domain_group_id') && $request->domain_group_id >= 0){
            //取孤兒domain
            if($request->domain_group_id == 0){
                $domains = $domains->filter(function ($item) {

                    return $item->domainGroup->isEmpty();
                });
            }
            
            if($request->domain_group_id > 0){
                $domains = $domains->filter(function ($item) use ($request){
                    $domainGroupId = $item->domainGroup()->pluck('domain_group_id');
                    return $domainGroupId->isEmpty() ? 0 : $domainGroupId[0] == $request->domain_group_id;
                });
            }
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
        //有 DomainGroup 並且 不能是 Group 內唯一的 Domain
        if(!$domain->domainGroup->isEmpty() && $domain->domainGroup->first()->domains->count() == 1){
            return $this->response('',PermissionError::CANT_DELETE_LAST_DOMAIN,[]);
        }

        //有 cdn 設定才要刪掉
        if(!$domain->cdns->isEmpty()){
            foreach($domain->cdns as $cdnModel){              
                event(new CdnWasDelete($cdnModel,1));
            }
        }
        $domain->delete();
        
        return $this->response('','',[]);
    }
}
