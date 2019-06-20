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
        $groupLists = $this->domainGroupRepository->indexByUserGroup($userGroupId);

        $groupLists = $groupLists->each(function ($item, $key) {
            $domainModel = $item->domains()->first();

            if( $domainModel== null){
                return false;
            }

            $cdn = $domainModel->cdns()->where('default',1)->first()->cdnProvider()->first();
            $item->setAttribute('default_cdn_name',$cdn->name);
        });

        return $groupLists;
    }
/**
 * 取得 DomainGroup 和 Domain 的關聯。在各自從 Domain 找 Cdn，再找 Cdn Provider 的名字。
 *
 * @param integer $domainGroup
 * @return void
 */
    public function indexByDomainGroupId(int $domainGroup)
    {
        $groupLists = $this->domainGroupRepository->indexByDomainGroupId($domainGroup);
        $domainCollection = $groupLists['domains'];

        $domainCollection = $domainCollection->each(function ($item,$key){
            $cdnCollection = $item->cdns()->get();
            
            if($cdnCollection == null){
                return false;
            }

            $cdnCollection = $cdnCollection->each(function ($item,$key){
                if($item->cdnProvider()->get() == null){
                    return false;
                }

                $cdnName = $item->cdnProvider()->get(['name']);
                $item->setAttribute('cdn_provider',$cdnName[0]);
            });
            $item->setAttribute('cdn',$cdnCollection);
        });

        return $domainCollection;
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

    public function destroyByDomainId(int $domainGroupId,int $domainId)
    {
        return $this->domainGroupRepository->destroyByDomainId($domainGroupId,$domainId);
    }
}