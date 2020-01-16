<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Hiero7\Services\DnsPodRecordSyncService;

class SyncDBToDNSPodRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:dnspod-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Database to DNSPod Records';

    protected $dnsPodRecordSyncService;

    protected $differentRecords, $syncCount = 10;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
        $this->syncCount = 10;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->checkDifferentStatus();

        if ($this->confirm('Do You Need Sync Records?')) {

            $this->syncRecordsByAction('Create', $this->differentRecords['create']);
            $this->syncRecordsByAction('Different', $this->differentRecords['different']);
            $this->syncRecordsByAction('Delete', $this->differentRecords['delete']);
        }

        $this->info("Sync Records Done !");
    }

    public function checkDifferentStatus()
    {
        $this->info("Check DB with DNSPod Records Different !");

        $this->differentRecords = $this->dnsPodRecordSyncService->getDifferentRecords();

        if (isset($data['error'])) {
            $this->error('DNS Pod Service Error!');

            print("\n");

            exit();
        } else {
            $this->showDiffernetRecords();
        }
    }

    private function showDiffernetRecords()
    {
        $this->info("DNS Pod Record different Count : " . $this->differentRecords['differentCount']);
        $this->info("DNS Pod Record need Create Count : " . $this->differentRecords['createCount']);
        $this->info("DNS Pod Record need Delete Count : " . $this->differentRecords['deleteCount']);
        $this->info("DNS Pod Record Match Count : " . $this->differentRecords['matchCount']);
    }

    private function syncRecordsByAction($action = 'Create', $records = [])
    {
        $total = count($records);

        $bar = $this->setProgressBar($total);

        $syncRecords = collect($records)->chunk($this->syncCount);

        $this->info("DNS Pod Record Sync By $action Records");

        $completedCount = 0;

        foreach ($syncRecords as $records) {

            $completed = $this->syncRecords($records->toArray(), $action);

            $bar->advance($completed);

            $completedCount += $completed;
        }

        if ($total == $completedCount) {
            $bar->finish();
        }else{

            $this->error("DNS Pod Record Sync By $action Records Only $completedCount !");
        }

        print("\n");
    }

    private function syncRecords(array $records = [], string $action)
    {
        $create = ($action == "Create") ? $records : [];

        $different = ($action == "Different") ? $records : [];

        $delete = ($action == "Delete") ? $records : [];

        $data = $this->dnsPodRecordSyncService->syncRecord(
            $create,
            $different,
            $delete
        );

        Log::info("[Sync DNSPod Record by $action] " . json_encode($data));

        if (!isset($data['error'])) {
            switch ($action) {
                case 'Create':
                    return count($data['data']['createSync']);
                    break;
                case 'Different':
                    return count($data['data']['diffSync']);

                    break;
                case 'Delete':

                    return count($data['data']['deleteSync']);
                    break;

                default:
                    return 0;
                    break;
            }
        }

        return 0;
    }

    private function setProgressBar(int $count = 0)
    {
        $bar = $this->output->createProgressBar($count);

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');

        $bar->setBarWidth(50);

        return $bar;
    }

    public function getMaxFrequency(...$arrays)
    {
        $max = 0;

        foreach ($arrays as $data) {
            $dataCount = count($data);

            $max = ($dataCount > $max) ? $dataCount : $max;
        }

        return $max;
    }

}
