<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Models\Domain;

class DomainController extends Controller
{

    public function __construct()
    {
    }

    /**
     * Get Domain By ID
     *
     * @param Domain $domain
     */
    public function getDomainById(Domain $domain)
    {
        $domain->toArray;
        $dnsPodDomain = env('DNS_POD_DOMAIN');
        return $this->response('', null, compact('domain', 'dnsPodDomain'));
    }

    /**
     * Get Domain function
     *
     * $request->user_group_id，預設為 login user_group_id (可選)
     *
     * 如果 login user_group_id == 1 && $request->user_group_id == null，則會得到 All Domain
     * @param Request $request
     * @param Domain $domain
     */
    public function getDomain(Request $request, Domain $domain)
    {
        $user_group_id = $this->getUgid($request);

        $domains = !$request->has('user_group_id') && $user_group_id == 1 ?
        $domain->with('cdns')->get() :
        $domain->with('cdns')->where(compact('user_group_id'))->get();

        $domains->toArray();
        $dnsPodDomain = env('DNS_POD_DOMAIN');
        return $this->response('', null, compact('domains', 'dnsPodDomain'));
    }

    public function create(Request $request, Domain $domain)
    {
        $request->merge([
            'user_group_id' => $this->getUgid($request),
            'cname' => $request->get('cname') ?? $request->get('name'),
        ]);

        $domain = $domain->create($request->all());
        return $this->response('', null, $domain);
    }

    public function editDomain(Request $request, Domain $domain)
    {
        $domain->update($request->only('name', 'cname', 'label', 'edited_by'));
        return $this->response('', null, $domain);
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();
        return $this->response();
    }

    /**
     * get User Group ID function
     *
     * 判斷是否能夠取得 $request->user_group_id
     *
     * $request->user_group_id == null ，給予 login User_group_id
     * 權限符合，給予 $request->user_group_id
     * 權限不符合，給予 login User_group_id
     *
     * @param Request $request
     * @return int
     */
    private function getUgid(Request $request)
    {
        $getPayload = $this->getJWTPayload();

        $ugid = (($getPayload['user_group_id'] == $request->get('user_group_id')) ||
            ($getPayload['user_group_id'] == 1)) ?
        $request->get('user_group_id', $getPayload['user_group_id']) :
        $getPayload['user_group_id'];

        return $ugid;
    }
}
