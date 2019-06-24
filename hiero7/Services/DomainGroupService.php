<?php

namespace Hiero7\Services;

use Hiero7\Repositories\DomainGroupRepository;
use Hiero7\Models\DomainGroup;
use Hiero7\Models\{Domain,LocationDnsSetting};

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
    public function indexByDomainGroupId(DomainGroup $domainGroup)
    {
        foreach ($domainGroup->domains as $key => $domain) {
            $domain->cdnProvider;
        }

        return $domainGroup;
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

    public function createDomainToGroup(array $request,DomainGroup $domainGroup)
    {
        $checkDomainSetting = $this->compareDomain($domainGroup,$request['domain_id']);
        
        if(!$checkDomainSetting){
            return false;
        }
        
        $this->followSetting($request['domain_id']);
        $result = $this->domainGroupRepository->createDomainToGroup($request,$domainGroup->id);

        return $checkDomainSetting;
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

    private function compareDomain(DomainGroup $domainGroup,$targetDomainId)
    {
        $controlDomain  = $domainGroup->domains; 
        $controlCdnProvider = $controlDomain['0']->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');
        $targetCdnProvider = Domain::find($targetDomainId)->cdns()->get(['cdn_provider_id'])->pluck('cdn_provider_id');

        $different = $controlCdnProvider->diff($targetCdnProvider);

        return !$different->isEmpty()? false: true;
    }

    private function followSetting(int $domainId)
    {
        //變更 Default CDN 使用 Leo 給的
        //變更 iRoute 設定
        $getOriginDnsSetting = LocationDnsSetting::where('domain_id')->get();
    }
}