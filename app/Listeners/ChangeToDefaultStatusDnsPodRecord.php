<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;
use Hiero7\Traits\OperationLogTrait;

class ChangeToDefaultStatusDnsPodRecord
{
    use OperationLogTrait;

    protected $dnsProviderService;

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

        if (!$event->cdn->locationDnsSetting->isEmpty() && $event->defaultCdn->cdnProvider->status != $event->cdn->cdnProvider->status) {

            return $this->dnsProviderService->batchEditRecord([
                'record_id' => $event->dnsPodDomainId,
                'change' => 'status',
                'change_to' => $event->defaultCdn->cdnProvider->status ? 'enable' : 'disable',
            ]);
        }

        return ['errorCode' => null];
    }
}
