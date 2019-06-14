<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\OperationLogTrait;

class DeleteDnsPodRecord
{
    use OperationLogTrait;

    protected $dnsProviderService, $locationDnsSettingService;

    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
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
            if ($this->checkDeleteRecord($deletePodRecord)) {
                $value->delete;
            }
        }
        return;
    }

    public function checkDeleteRecord($deleteInfo)
    {
        if (!is_null($deleteInfo['errorCode']) || array_key_exists('errors', $deleteInfo)) {
            return false;
        }
        return true;
    }
}
