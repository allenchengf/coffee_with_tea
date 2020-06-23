<?php

namespace Hiero7\Repositories;

use Hiero7\Models\ChangeLogForPortal;
use Hiero7\Models\Permission;

class ChangeLogForPortalRepository
{
    protected $changeLogForPortal;

    public function __construct(ChangeLogForPortal $changeLogForPortal)
    {
        $this->changeLogForPortal = $changeLogForPortal;
    }

    public function getLogByTime(string $startTime, string $endTime)
    {
        return $this->changeLogForPortal->whereBetween('created_at', [
            $startTime,
            $endTime,
        ])->get();
    }
}