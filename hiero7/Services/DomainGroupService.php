<?php

namespace Hiero7\Services;

use Hiero7\Repositories\DomainGroupRepository;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\Domain;

class DomainGroupService
{
    protected $domainGroupRepository;

    public function __construct(DomainGroupRepository $domainGroupRepository)
    {
        $this->domainGroupRepository = $domainGroupRepository;
    }

    public function index(int $userGroupId)
    {
        $groupLists = $this->domainGroupRepository->index($userGroupId);

        $groupLists = $groupLists->each(function ($item, $key) {
            if($item->domains()->first() == null){
                return false;
            }
            $cdn = $item->domains()->first()->cdns()->where('default',1)->first()->cdnProvider()->first();
            $item->setAttribute('default_cdn_name',$cdn->name);
        });

        return $groupLists;
    }

    public function domainLists($userGroupId)
    {
        $exist = $this->domainGroupRepository->index($userGroupId);

        return ;
    }
/**
 * Create Group function
 *  
 * 先檢查 Domain 和 操作人的 user_group_id 是否相同，若 user_group_id ＝ 1 例外。
 * 若 user_group_id = 1 會依照欲加入的 Domain 的 user_group_id 給與新建立的 Group 相同 user_group_id。
 * @param array $request
 */
    public function create(array $request)
    {
        $domainIdGroup = Domain::where('id',$request['domain_id'])->first();
        $request['user_group_id'] == 1 ? $request['user_group_id'] = $domainIdGroup->user_group_id : $request['user_group_id'];
        
        if($request['user_group_id']!= $domainIdGroup->user_group_id){
            return 'differentGroup';
        }

        if(!$this->domainGroupRepository->create($request)){
            return 'exist';
        }

        return 'done';
    }

    public function edit(array $request,DomainGroup $domainGroup)
    {
        $domain = $domainGroup->domains()->first();
        if($request['user_group_id'] != 1 && $request['user_group_id'] != $domain->user_group_id){
            return false;
        }

        return $this->domainGroupRepository->update($request,$domainGroup->id);
    }

    public function destroy(int $domainGroupId)
    {
        return $this->domainGroupRepository->destroy($domainGroupId);
    }
}