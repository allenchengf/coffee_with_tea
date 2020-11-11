<?php

namespace Hiero7\Repositories;

use Hiero7\Models\{DomainGroup,DomainGroupMapping};
use App\Http\Requests\DomainGroupRequest;

class DomainGroupRepository
{
    protected $domainGroupModel;

    public function __construct(DomainGroup $domainGroupModel)
    {
        $this->domainGroupModel = $domainGroupModel;
    }

    public function indexByUserGroup(int $userGroupId)
    {
        return $this->domainGroupModel
//            ->with('domains')
            ->where('user_group_id',$userGroupId)->get();
    }

    public function showByDomainGroupId(int $domainGroupId)
    {
        return $this->domainGroupModel->with('domains')->where('id',$domainGroupId)->first();
    }

/**
 * Create function
 *
 * 檢查是否已有，若已存在 $checkExist = false。
 * 先新增 domain_group table ，再新增中間表(domain_group_mapping)。
 * @param array $request
 */
    public function create(DomainGroupRequest $request)
    {
        $checkExist = $this->domainGroupModel->where('name',$request->name)->where('user_group_id',$request->user_group_id)->get()->isEmpty();
        if (!$checkExist){
            return false;
        }

        $domainGroup = $this->domainGroupModel->create([
            'user_group_id' => $request->user_group_id,
            'name' => $request->name,
            'label' => $request->label,
            'edited_by' => $request->edited_by,
        ]);
        
        $this->domainGroupModel->find($domainGroup->id)->domains()->attach($request->domain_id);
        return  $domainGroup;      
    }

    public function createDomainToGroup(DomainGroupRequest $request,int $domainGroupId)
    {
        return DomainGroupMapping::create([
            'domain_id' => $request->domain_id,
            'domain_group_id' => $domainGroupId
        ]);
    }

    public function update(DomainGroupRequest $request,int $domainGroupId)
    {
        return $this->domainGroupModel->where('id',$domainGroupId)->update([
            "name" => $request->name,
            "label" => $request->label
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