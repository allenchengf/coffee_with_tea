<?php

namespace Hiero7\Repositories;

use Hiero7\Models\{Group,DomainGroup};

class GroupRepository
{
    protected $groupModel;
    protected $domainGroupModel;

    public function __construct(Group $groupModel,DomainGroup $domainGroupModel)
    {
        $this->groupModel = $groupModel;
        $this->domainGroupModel = $domainGroupModel;
    }

    public function index()
    {
        return  $this->groupModel->with('domains')->get();
    }

    public function create(array $request)
    {
        $id = $this->groupModel->create([
            'name' => $request['name'],
            'label' => $request['label'],
            'edited_by' => $request['edited_by']
        ])->id;

        return  $this->groupModel->find($id)->domains()->attach($request['domain_id']);        
    }

}