<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Services\ProcessService;
use Hiero7\Models\Job;
use Illuminate\Support\Facades\Redis;


class ProcessServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->job = new Job;
        $this->service = new ProcessService($this->job);
        $this->functionName = 'batchCreateDomainAndCdn';
        $this->editedBy = 'ProcessTest';
        $this->ugId = 1;
        $this->queueName = $this->functionName.$this->editedBy.$this->ugId;
        $this->count = 2;
        $this->redisJob = Redis::connection('jobs');
        $this->redisRecord = Redis::connection('record');
        app()->call([$this, 'fakeDataForGetProcess']);
        app()->call([$this,'fakeDataForGetRecord']);
    }


    /**
     * 驗證 job 剛加進去 queue 的情況下，取得進度條
     * Done 不會是負數
     * All 會等於 Process
     *
     * @return void
     */
    public function testIndex()
    {
        $request = [ 'function_name' => $this->functionName,
                        'edited_by' => $this->editedBy,
                    ];

        $response = $this->service->index($request, $this->ugId);

        $this->assertEquals($response['done'], 0);
        $this->assertEquals($response['all'], $response['process']);
    }
    
    /**
     * 驗證 job 都處理完畢的情況下，取得進度條
     * Done 會等於 All
     * Process 會是 0 不會是負數
     *
     * @return void
     */
    public function testIndexAllDone()
    {        
        $this->job->where('queue',$this->queueName)->delete();
        

        $request = [ 'function_name' => $this->functionName,
                        'edited_by' => $this->editedBy,
                    ];
        
        $response = $this->service->index($request, $this->ugId);

        $this->assertEquals($response['done'], $this->count);
        $this->assertEquals($response['process'], 0);
        $this->assertEquals($response['all'], $response['done']);
    }

    /**
     * 驗證當 Process 都跑完的情況下，取得進度條
     * Done 和 All 會是一樣的數字
     * Process 會是 0
     *
     * @return void
     */
    public function testIndexWhenProcessDone()
    {
        $this->fakeDoneProcess();

        $request = [ 'function_name' => $this->functionName,
        'edited_by' => $this->editedBy,
    ];

        $response = $this->service->index($request, $this->ugId);

        $this->assertEquals($response['process'], 0);
        $this->assertEquals($response['all'], $response['done']);
    }

    /**
     * 驗證一般的情況下，取得處理結果
     * 會有 success 和 failure
     *
     * @return void
     */
    public function testGetBatchResult()
    {
        $request = [ 'function_name' => $this->functionName,
                        'edited_by' => $this->editedBy,
                    ];

        $response = $this->service->getBatchResult($request, $this->ugId);

        $this->assertArrayHasKey('success',$response);
        $this->assertCount(2,$response['success']['domain']);

        $this->assertArrayHasKey('failure',$response);
        $this->assertCount(3,$response['failure']['domain']);
    }

    /**
     * 驗證當 Process 全部跑完的情況下，第二次 取得處理結果
     * Success 和 Failure 會是空的
     * 
     * @return void
     */
    public function testGetBatchResultWhenProcessDone()
    {
        $this->fakeDoneProcess();

        $request = [ 'function_name' => $this->functionName,
                    'edited_by' => $this->editedBy,
                    ];

        //假裝是前端打 getProcess (主要是觸發內部的「當 Process 每筆跑完 」邏輯)
        $this->service->index($request, $this->ugId);

        //GetBatchResult 第一次
        $response = $this->service->getBatchResult($request, $this->ugId);

        $this->assertArrayHasKey('success',$response);
        $this->assertCount(2,$response['success']['domain']);

        $this->assertArrayHasKey('failure',$response);
        $this->assertCount(3,$response['failure']['domain']);

        //GetBatchResult 第二次
        $response1 = $this->service->getBatchResult($request, $this->ugId);

        $this->assertArrayHasKey('success',$response1);
        $this->assertCount(0,$response1['success']['domain']);

        $this->assertArrayHasKey('failure',$response1);
        $this->assertCount(0,$response1['failure']['domain']);

    }


    /**
     * 假資料：主要是給 取得進度 用的
     *
     * @return void
     */
    public function fakeDataForGetProcess()
    {
        $data =[[
                'id' => 1,
                'queue' => $this->queueName,
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ],[
                'id' => 2,
                'queue' => $this->queueName,
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ]];

        $this->job->insert($data);

        $this->redisJob->set($this->queueName,$this->count);
    }

    /**
     * 假資料：主要是給 取得處理結果 用的
     *
     * @return void
     */
    public function fakeDataForGetRecord()
    {
        $fakeData = [[ "success" => ['domain'=>[]],
                        "failure" => ['domain' => [
                                            ["name" => "yuan5.com",
                                            "errorCode" => 111,
                                            "message" => "This domain has been stored with no cdns.",
                                            "cdn" =>[]
                                            ]]
                                        ]
                        ],
                    [ "success" => ['domain'=>[["name" => "yuan4.com","cdn" =>[]]]],
                        "failure" => ['domain' =>
                                            [["name" => "yuan4.com",
                                            "errorCode" => 111,
                                            "message" => "This domain has been stored with no cdns.",
                                            "cdn" =>[
                                                    ["name" => "Hiero7",
                                                    "errorCode"=> 113,
                                                    "message" => "hiero11.yuan.com hiero11.yuan.com  for hiero11.yuan.com"
                                                    ],
                                                    ["name" => "Hiero77",
                                                    "errorCode"=> 113,
                                                    "message" => "hiero17.yuan.com hiero17.yuan.com  for hiero17.yuan.com"
                                                    ]
                                                ]
                                            ]]
                                        ]
                        ],
                    ["success" => ['domain'=>[["name" => "yuan3.com","cdn" =>[]]]],
                        "failure" => ['domain' => 
                                            [["name" => "yuan3.com",
                                            "errorCode" => 111,
                                            "message" => "This domain has been stored with no cdns.",
                                            "cdn" =>[["name" => "Hiero7","errorCode"=> 113,"message" => "hiero10.yuan.com hiero10.yuan.com  for hiero10.yuan.com"]]
                                            ]]
                                        ]
                        ]
                ];

        if($this->redisRecord->lrange($this->queueName,0,-1) == null)
        {
            foreach($fakeData as $data){
                $this->redisRecord->lpush($this->queueName,json_encode($data));
                }
        }
    }

    /**
     * 假裝所有 process 都處理完成
     *
     * @return void
     */
    private function fakeDoneProcess()
    {
        $this->job->where('queue',$this->queueName)->delete();
    }
}
