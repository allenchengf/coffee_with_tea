<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Artisan;


class CallWorker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $queueName, $count;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $i = 0;
        // while($i < $this->count){
            Artisan::call('queue:work', [ 'connection' => 'database',
            '--queue' => $this->queueName , '--once' => true
            ]);
sleep(2);
        //     $result[] = Artisan::output();
        // }
// echo $result;
        return;
    }

    public function failed(Exception $exception = null)
    {
        
    }
}
