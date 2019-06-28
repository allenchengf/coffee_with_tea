<?php

namespace Hiero7\Repositories;

use Hiero7\Models\{DomainGroup,DomainGroupMapping};
class DomainGroupRepository
{
    protected $domainGroupModel;

    public function __construct(DomainGroup $domainGroupModel)
    {
        $this->domainGroupModel = $domainGroupModel;
    }

    public function indexByUserGroup(int $userGroupId)
    {
        if($userGroupId == 1){
            return  $this->domainGroupModel->with('domains')->get();
        }

        return $this->domainGroupModel->with('domains')->where('user_group_id',$userGroupId)->get();
    }

/**
 * Create function
 *
 * 檢查是否已有，若已存在 $checkExist = false。
 * 先新增 domain_group table ，再新增中間表(domain_group_mapping)。
 * @param array $request
 */
    public function create(array $request)
    {
        $checkExist = $this->domainGroupModel->where('name',$request['name'])->where('user_group_id',$request['user_group_id'])->get()->isEmpty();
        if (!$checkExist){
            return false;
        }

        $domainGroup = $this->domainGroupModel->create([
            'user_group_id' => $request['user_group_id'],
            'name' => $request['name'],
            'label' => $request['label'],
            'edited_by' => $request['edited_by'],
            'created_at' => \Carbon\Carbon::now()
        ]);
        
        $this->domainGroupModel->find($domainGroup->id)->domains()->attach($request['domain_id']);
        return  $domainGroup;      
    }

    public function createDomainToGroup(array $request,int $domainGroupId)
    {
        return DomainGroupMapping::create([
            'domain_id' => $request['domain_id'],
            'domain_group_id' => $domainGroupId,
            'created_at' => \Carbon\Carbon::now()
        ]);
    }

    public function update(array $request,int $domainGroupId)
    {
        return $this->domainGroupModel->where('id',$domainGroupId)->update([
            "name" => $request['name'],
            "label" => $request['label']
        ]);
    }

    public function destroy(int $domainGroupId)
    {
        return $this->domainGroupModel->find($domainGroupId)->delete();
        
    }

    public function destroyByDomainId(int $domainGroupId,int $domainId)
    {
        return DomainGroupMapping::where('domain_group_id',$domainGroupId)->where('domain_id',$domainId)->delete();
        
    }
}