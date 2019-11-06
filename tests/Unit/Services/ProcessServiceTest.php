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
        $this->seed('JobTableSeeder');
        $this->job = new Job;
        $this->service = new ProcessService($this->job);
        $this->functionName = 'batchCreateDomainAndCdn';
        $this->editedBy = 'ProcessTest';
        $this->ugId = 1;
        $this->redis = Redis::connection('jobs');
        app()->call([$this, 'insertProcess']);
    }


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $request = [ 'function_name' => $this->functionName,
                        'edited_by' => $this->editedBy,
                    ];

        $response = $this->service->index($request, $this->ugId);
dd($response);
    }

    public function insertProcess()
    {
        $data =[
                'queue' => $this->functionName.$this->editedBy.$this->ugId,
                'payload' => '',
                'attempts' => 0,
                'reserved_at' => \Carbon\Carbon::now()->timestamp,
                'available_at' => \Carbon\Carbon::now()->timestamp,
                'created_at' => \Carbon\Carbon::now()->timestamp,
        ];

        $this->job->insert($data);

        $this->redis->set($this->functionName.$this->editedBy.$this->ugId,1);

    }
}
