<?php

namespace App\Console\Commands;

use Hiero7\Services\DnsPodRecordSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDBToDNSPodRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:dnspod-records {--sync-max=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Database to DNSPod Records';

    protected $dnsPodRecordSyncService;

    protected $differentRecords, $syncMaxLimit = 10;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DnsPodRecordSyncService $dnsPodRecordSyncService)
    {
        $this->dnsPodRecordSyncService = $dnsPodRecordSyncService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setSyncMaxLimit();

        $this->checkDifferentStatus();

        if ($this->confirm('Do You Need Sync Records?')) {

            $this->syncRecordsByAction('Create', $this->differentRecords['create']);

            $this->syncRecordsByAction('Different', $this->differentRecords['different']);

            $this->syncRecordsByAction('Delete', $this->differentRecords['delete']);
        }

        $this->info("Sync Records Done !");
    }

    /**
     * 檢查 DNSPod Records Status
     *
     * @return void
     */
    public function checkDifferentStatus()
    {
        $this->info("Check DB with DNSPod Records Different !");

        $this->differentRecords = $this->dnsPodRecordSyncService->getDifferentRecords();

        if (isset($this->differentRecords['error'])) {
            $this->error('DNS Pod Service Error!');

            print("\n");

            exit();
        } else {
            $this->showDiffernetRecords();
        }
    }

    /**
     * Show DNSPod Records Status
     *
     * @return void
     */
    private function showDiffernetRecords()
    {
        $this->info("DNS Pod Record different Count : " . $this->differentRecords['differentCount']);
        $this->info("DNS Pod Record need Create Count : " . $this->differentRecords['createCount']);
        $this->info("DNS Pod Record need Delete Count : " . $this->differentRecords['deleteCount']);
        $this->info("DNS Pod Record Match Count : " . $this->differentRecords['matchCount']);
    }

    /**
     * Sync Records By Action
     *
     * @param string $action [Create|Different|Delete]
     * @param array $records
     * @return void
     */
    private function syncRecordsByAction($action = 'Create', $records = [])
    {
        $total = count($records);

        $bar = $this->setProgressBar($total);

        $syncRecords = collect($records)->chunk($this->syncMaxLimit);

        $this->info("DNS Pod Record Sync By $action Records");

        $completedCount = 0;

        foreach ($syncRecords as $records) {

            $completed = $this->syncRecords($records->toArray(), $action);

            $bar->advance($completed);

            $completedCount += $completed;
        }

        if ($total == $completedCount) {
            $bar->finish();
        } else {
            $this->error("DNS Pod Record Sync By $action Records Only $completedCount !");
        }

        print("\n");
    }

    /**
     * Sync Records 
     * 
     * Return Complete Count
     *
     * @param array $records
     * @param string $action
     * @return integer
     */
    private function syncRecords(array $records = [], string $action): int
    {
        $create = ($action == "Create") ? $records : [];

        $different = ($action == "Different") ? $records : [];

        $delete = ($action == "Delete") ? $records : [];

        $syncOutputData = $this->dnsPodRecordSyncService->syncRecord(
            $create,
            $different,
            $delete
        );

        Log::info("[Sync DNSPod Record by $action] " . json_encode($syncOutputData));

        if (!isset($syncOutputData['error'])) {
            switch ($action) {
                case 'Create':
                    return count($syncOutputData['data']['createSync']);
                    break;
                case 'Different':
                    return count($syncOutputData['data']['diffSync']);
                    break;
                case 'Delete':
                    return count($syncOutputData['data']['deleteSync']);
                    break;
                default:
                    return 0;
                    break;
            }
        }

        return 0;
    }

    /**
     * 設定進度條
     *
     * @param integer $count
     * @return void
     */
    private function setProgressBar(int $quantity = 0)
    {
        $bar = $this->output->createProgressBar($quantity);

        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');

        $bar->setBarWidth(50);

        return $bar;
    }

    /**
     * 設定每次 Sync Records 最大數量
     *
     * @return void
     */
    private function setSyncMaxLimit(): void
    {
        $this->syncMaxLimit = $this->option('sync-max');
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
