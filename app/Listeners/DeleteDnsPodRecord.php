<?php

namespace App\Listeners;

use App\Jobs\DeleteDnsPodRecord as DeleteJob;
use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\OperationLogTrait;

class DeleteDnsPodRecord
{
    use OperationLogTrait;

    protected $dnsProviderService, $deleteErrorCount;

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
        $this->deleteErrorCount = false;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $locationDnsSetting = $event->cdn->locationDnsSetting;
        if ($locationDnsSetting->isEmpty()) {
            return;
        }

        foreach ($locationDnsSetting as $key => $value) {
            $deletePodRecord = $this->dnsProviderService->deleteRecord([
                'record_id' => $value->provider_record_id,
            ]);
            if ($this->dnsProviderService->checkAPIOutput($deletePodRecord)) {
                $value->delete();
            } else {
                $this->deleteErrorCount = true;
            }
        }

        if ($this->deleteErrorCount) {
            DeleteJob::dispatch()->delay(300);
        }

        return;
    }
}
