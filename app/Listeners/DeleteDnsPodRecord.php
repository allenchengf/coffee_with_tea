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
        $locationDnsSettings = $event->cdn->locationDnsSetting;
        if (!$locationDnsSettings->isEmpty()) {
            foreach ($locationDnsSettings as $locationDnsSetting) {
                $deleteResponse = $this->dnsProviderService->deleteRecord([
                    'record_id' => $locationDnsSetting->provider_record_id,
                ]);

                if ($this->dnsProviderService->checkAPIOutput($deleteResponse)) {
                    $locationDnsSetting->delete();
                } else {
                    $this->deleteErrorCount = true;
                }
            }
        }

        if(!$this->deleteErrorCount && $event->deleteDefault == 1 && $event->cdn->default == 1){
            $deleteDefaultResponse = $this->dnsProviderService->deleteRecord([
                'record_id' => $event->cdn->provider_record_id,
            ]);

            if (!$this->dnsProviderService->checkAPIOutput($deleteDefaultResponse)) {
                $this->deleteErrorCount = true;
            }
        }

        if ($this->deleteErrorCount) {
            DeleteJob::dispatch()->delay(300);
            return false;
        }

        $event->cdn->delete();

        return true;
    }
}
