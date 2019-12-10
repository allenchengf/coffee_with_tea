<?php

namespace Hiero7\Services;

use Illuminate\Support\Facades\Redis;
use Hiero7\Models\Job;
use Illuminate\Support\Collection;


class ProcessService
{
    public function __construct(Job $jobs)
    {
        $this->jobs = $jobs;
        $this->redis =  Redis::connection('jobs');
    }

    public function index(array $request, $ugId)
    {
        $this->getQueueName($request, $ugId);

        $all = (int) $this->redis->get($this->queueName);
        $process = $this->jobs->where('queue', $this->queueName)->count();
        $done = ($all - $process) < 0 ? 0 : $all - $process ;

        $result = ['all' => $all,
                    'process' => $process,
                    'done' => $done
                ];

        return $result;
    }

    /**
     * 取出該 function 的 batch 結果
     *
     * @param array $request
     * @return void
     */
    public function getBatchResult(array $request, $ugId)
    {
        $this->getQueueName($request, $ugId);
        $connect = Redis::connection('record');

        $error = $connect->lrange($this->queueName,0,-1);

        list($success,$failure) = $this->format($error);

        $result = ['success'=> $success,
                    'failure' => $failure
                    ];

        $this->deleteRedisRecord($connect);

        return $result;
    }

    /**
     * 檢查如果每一筆 job 資料都完成的話，就 刪掉 Record 的記錄
     *
     * @param [type] $connect
     * @return void
     */
    private function deleteRedisRecord($connect)
    {
        $process = $this->jobs->where('queue', $this->queueName)->count();
        
        if($process == 0)
        {
            $connect->del($this->queueName);
            $this->redis->del($this->queueName);
        }
    }

    /**
     * 整理資料，將每筆資料都轉成 Array 的格式
     *
     * @param array $result
     * @return void
     */
    private function format(array $result)
    {
        $all = [];

        foreach($result as $count){
            $all[] = json_decode($count);
        }

        list($success,$failure) = $this->formatArray(collect($all));

        return [$success,$failure];
    }

    /**
     * 整理資料。區分 Success 和 Failure
     *
     * @param Collection $data
     * @return void
     */
    private function formatArray(Collection $data)
    {
        $domainSuccess = $domainFailure = [];

        foreach($data as $array){
            if(!empty($array->success->domain)){
                $domainSuccess[]= $array->success->domain;
            }

            if(!empty($array->failure->domain)){
                $domainFailure[]= $array->failure->domain;
            }
        }

        $domainSuccess = $this->removeEmpty(collect($domainSuccess)->collapse());
        $domainFailure = $this->removeEmpty(collect($domainFailure)->collapse());

        $success = ['domain' => $domainSuccess];
        $failure = ['domain' => $domainFailure];

        return [ $success, $failure];
    }

    /**
     * 移除空的
     *
     * @param Collection $data
     * @return void
     */
    private function removeEmpty(Collection $data)
    {
        $result = [];

        foreach($data as $array){
            if(!empty($array)){
                $result[] = $array;
            }
        }

        return $result;

    }

    private function getQueueName(array $request, $ugId)
    {
        $this->queueName = $request['function_name'].$request['edited_by'].$ugId;
    }
}