<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Artisan;
use Illuminate\Support\Facades\Log;


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
        try{
            Artisan::call('queue:work', [ 'connection' => 'database',
            '--queue' => $this->queueName , '--once' => true
            ]);

            Log::info('[CallWorker] queueName:'. $this->queueName .'Artisan call done.');

        }catch (Exception $e){
            $this->failed($e);
        }

        return;
    }

    public function failed($exception = null)
    {
        Log::info('[CallWorker] Error: ' . $exception);
        $this->delete();
    }
}
