<?php

namespace Hiero7\Services;

use Illuminate\Support\Facades\Redis;
use Hiero7\Models\Job;


class ProcessService
{
    public function __construct(Job $jobs)
    {
        $this->jobs = $jobs;
    }

    public function index(array $request, $ugId)
    {
        $queueName = $request['function_name'].$request['edited_by'].$ugId;

        $redis = Redis::connection('jobs');

        $all = (int) $redis->get($queueName);
        $process = $this->jobs->where('queue', $queueName)->count();
        $done = ($all - $process) < 0 ? 0 : $all - $process ;

        if($process == 0)
        {
            $redis->del($queueName);
        }

        $result = ['all' => $all,
                    'process' => $process,
                    'done' => $done
                ];

        return $result;
    }
}