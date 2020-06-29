<?php

namespace App\Console\Commands;

use Hiero7\Repositories\ChangeLogForPortalRepository;
use Illuminate\Console\Command;

class DeleteInvalidLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:delete-invalid-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Invalid Log';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChangeLogForPortalRepository $changeLogForPortalRepository)
    {
        parent::__construct();
        $this->changeLogForPortalRepository = $changeLogForPortalRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $days = env('LOG_INVALID_DAY', 5);

        $count = $this->changeLogForPortalRepository->deleteInvalid($days);

        $this->info("Delete Data Count : " . $count);
    }
}
