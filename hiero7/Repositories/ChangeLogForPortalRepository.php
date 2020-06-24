<?php

namespace Hiero7\Repositories;

use Carbon\Carbon;
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

    public function latestLogByCount(int $count = 50)
    {
        return $this->changeLogForPortal->latest()
            ->limit($count)->get();
    }

    public function deleteInvalid(int $days = 5)
    {
        return $this->changeLogForPortal->where('created_at', '<', Carbon::now()->subDays($days))->delete();
    }
}