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
        $this->count = 2;
        $this->redis = Redis::connection('jobs');
        app()->call([$this, 'insertProcess']);
    }


    /**
     * 驗證 job 剛加進去 queue 的情況下
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
     * 驗證 job 都處理完畢的情況
     * Done 會等於 All
     * Process 會是 0 不會是負數
     *
     * @return void
     */
    public function testIndexAllDone()
    {        
        $this->job->where('queue',$this->functionName.$this->editedBy.$this->ugId)->delete();
        

        $request = [ 'function_name' => $this->functionName,
                        'edited_by' => $this->editedBy,
                    ];
        
        $response = $this->service->index($request, $this->ugId);

        // dd($response, $this->redis->get($this->functionName.$this->editedBy.$this->ugId));

        $this->assertEquals($response['done'], $this->count);
        $this->assertEquals($response['process'], 0);
        $this->assertEquals($response['all'], $response['done']);
    }

    public function insertProcess()
    {
        $data =[[
                'id' => 1,
                'queue' => $this->functionName.$this->editedBy.$this->ugId,
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ],[
                'id' => 2,
                'queue' => $this->functionName.$this->editedBy.$this->ugId,
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
            ]];

        $this->job->insert($data);

        $this->redis->set($this->functionName.$this->editedBy.$this->ugId,$this->count);

    }
}
