<?php

namespace App\Http\Controllers\Api\v1;

use DB;
use App\Events\CdnWasDelete;
use App\Http\Controllers\Controller;
use App\Http\Requests\DomainRequest as Request;
use Hiero7\Enums\InputError;
use Hiero7\Enums\PermissionError;
use Hiero7\Models\Domain;
use Hiero7\Services\DomainService;
use Hiero7\Traits\OperationLogTrait;
use Hiero7\Traits\PaginationTrait;

class DomainController extends Controller
{
    use OperationLogTrait;
    use PaginationTrait;
    protected $domainService;
    protected $status;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;

        $this->status = (env('APP_ENV') !== 'testing') ?? false;

        $this->setCategory(config('logging.category.domain'));
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

        if ($request->has('domain_group_id') && $request->domain_group_id >= 0) {
            //取孤兒domain
            if ($request->domain_group_id == 0) {
                $domains = $domains->filter(function ($item) {

                    return $item->domainGroup->isEmpty();
                });
            }

            if ($request->domain_group_id > 0) {
                $domains = $domains->filter(function ($item) use ($request) {
                    $domainGroupId = $item->domainGroup()->pluck('domain_group_id');
                    return $domainGroupId->isEmpty() ? 0 : $domainGroupId[0] == $request->domain_group_id;
                });
            }
            $domains = $domains->values();
        }

        $dnsPodDomain = env('DNS_POD_DOMAIN');

        // 換頁
        // 初始換頁資訊
        $last_page                                        = $current_page                                        = $per_page                                        = $total                                        = null;
        list($perPage, $columns, $pageName, $currentPage) = $this->getPaginationInfo($request->per_page, $request->current_page);

        if (!is_null($perPage)) { // 換頁
            $current_page = $currentPage;
            $total        = $domains->count();
            $last_page    = ceil($total / $perPage);
            $per_page     = $perPage;

            $domains = $domains->forPage($currentPage, $perPage)->all();
        } else { // 全部列表
                     //
        }

        return $this->response('', null, compact('current_page', 'last_page', 'per_page', 'total', 'domains', 'dnsPodDomain'));
    }


    public function getDomainSqlJoin(Request $request)
    {
        // 取得換頁資訊
        list($perPage, $columns, $pageName, $currentPage) = $this->getPaginationInfo($request->get('per_page'), $request->get('current_page'));

        // SQL
        $query = DB::table('domains')
                    // 一對多 cdns
                    ->leftjoin('cdns', 'domains.id', '=', 'cdns.domain_id')
                    ->groupBy('cdns.domain_id')
                    // 多對多 domain_groups
                    ->leftjoin('domain_group_mapping', 'domains.id', '=', 'domain_group_mapping.domain_id')
                    ->leftjoin('domain_groups', 'domain_group_mapping.domain_group_id', '=', 'domain_groups.id')
                    // 欄位篩選
                    ->select('domains.id', /*'domains.user_group_id',*/ 'domains.name', 'domains.cname', 'domains.label')
                    ->addSelect('domain_groups.id as group_id', 'domain_groups.name as group_name')
//                    ->addSelect(DB::raw('group_concat(cdns.cdn_provider_id) as cdn_provider_id, group_concat(cdns.cname) as cdn_cname, group_concat(cdns.default) as cdn_default'))
                    ->addSelect(DB::raw('group_concat(cdns.cdn_provider_id) as cdn_provider_id, group_concat(cdns.cname) as cdn_cname, group_concat(cdns.default) as cdn_default, group_concat(cdns.id) as cdn_id'))
                    // 排序
                    ->orderBy('domains.id', 'asc');

        // 條件限定 domain_group_id
        if ($request->has('domain_group_id')) {
            $domain_group_id = $request->domain_group_id;
            if ($domain_group_id > 0) { // 限定 domain_group_id
                $query = $query->where('domain_groups.id', $domain_group_id);
            }
            elseif ($domain_group_id == 0) { // 而 0 是只取孤兒 domain
                $query = $query->whereNull('domain_groups.id');
            }
        } // else: 不給 domain_group_id 預設為 all Group (含孤兒 Domain)

        // 條件限定 user_group_id
        $user_group_id = $this->getUgid($request);
        if ($user_group_id > 1) {
            $query = $query->where('domains.user_group_id', $user_group_id);
        }
        elseif ($user_group_id == 1 &&  $request->has('user_group_id')) { // Hiero7 全給或依 user_group_id 篩選
            $query = $query->where('domains.user_group_id', $request->user_group_id);
        }

        // 搜尋
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query = $query->where(function($q) use (&$search){
                            $q->orWhere('domains.name', 'like', "%$search%")
                            ->orWhere('domains.cname', 'like', "%$search%")
                            ->orWhere('domains.label', 'like', "%$search%");
                    });
        }

        // 分頁
        if(! is_null($perPage)){
            $domains = $query->paginate($perPage, $columns, $pageName, $currentPage);

            $last_page = $domains->lastPage();
            $current_page = $domains->currentPage();
            $per_page = $perPage;
            $total = $domains->total();
            $domains = $domains->items();
        } else { // 不分頁
            $domains = $query->get();

            $last_page = 1;
            $current_page = 1;
            $per_page = 1;
            $total = $domains->count();
        }

        $dnsPodDomain = env('DNS_POD_DOMAIN');

        return $this->response('', null, compact('current_page', 'last_page', 'per_page', 'total', 'domains', 'dnsPodDomain'));
    }

    public function countDomain(Request $request)
    {
        $user_group_id = $this->getUgid($request);

        // SQL
        $query = DB::table('domains')->select(DB::raw('count(id) as total'));

        if ($user_group_id != 1) { // Hiero7 全給
            $query = $query->where('user_group_id', $user_group_id);
        }

        $data = $query->first();

        return $this->response('', null, ['total' => $data->total]);
    }

    public function create(Request $request, Domain $domain)
    {
        $ugid = $this->getUgid($request);

        $this->modifyName($request);

        $request->merge([
            'user_group_id' => $ugid,
            'cname'         => $this->domainService->cnameFormat($request, $ugid),
        ]);

        if (!$errorCode = $this->domainService->checkUniqueCname($request->cname)) {
            $domain = $domain->create($request->all());

            $this->setChangeTo($domain->saveLog())->createOperationLog();
        }

        return $this->setStatusCode($errorCode ? 400 : 200)->response(
            '',
            $errorCode ? $errorCode : null,
            $errorCode ? [] : $domain
        );

    }

    public function editDomain(Request $request, Domain $domain)
    {
        $this->setChangeFrom($domain->saveLog());

        $this->modifyName($request);

        $domain->update($request->only('label', 'edited_by'));

        $domain->cdns;

        $this->setChangeTo($domain->saveLog())->createOperationLog();

        return $this->response('', null, $domain);
    }

    public function destroy(Domain $domain)
    {
        $this->setChangeFrom($domain->saveLog());

        $domain_name = $domain->name;

        $deleteRestult = true;

        //有 DomainGroup 並且 不能是 Group 內唯一的 Domain
        if (!$domain->domainGroup->isEmpty() && $domain->domainGroup->first()->domains->count() == 1) {
            return $this->setStatusCode(400)->response('', PermissionError::CANT_DELETE_LAST_DOMAIN, compact('domain_name'));
        }

        $domain->domainGroup()->detach();

        //有 cdn 設定才要刪掉
        if (!$domain->cdns->isEmpty()) {
            foreach ($domain->cdns as $cdnModel) {
                $result = event(new CdnWasDelete($cdnModel, 1));

                $deleteRestult = ($deleteRestult == false || $result == false) ? false : true;
            }
        }

        if (!$deleteRestult) {
            return $this->setStatusCode(400)->response('', InputError::PLEASE_DELETE_DOMAIN_AGAIN, compact('domain_name'));
        }

        $domain->delete();
        $this->createOperationLog();

        return $this->response('', '', compact('domain_name'));
    }

    private function modifyName(Request $request)
    {
        if ($request->has('name')) {
            $request->merge([
                'name' => strtolower($request->get('name')),
            ]);
        }
    }
}
