<?php

namespace Hiero7\Services;

use Hiero7\Repositories\GroupRepository;

class GroupService
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function index()
    {
        return $this->groupRepository->index();
    }

    public function create(array $request)
    {
        return $this->groupRepository->create($request);
    }
}