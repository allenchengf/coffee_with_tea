<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Traits\OperationLogTrait;

class ChangeToDefaultDnsPodRecord
{
    use OperationLogTrait;

    protected $dnsProviderService, $locationDnsSettingService;

    public function __construct(DnsProviderService $dnsProviderService, LocationDnsSettingService $locationDnsSettingService)
    {
        $this->dnsProviderService = $dnsProviderService;
        $this->locationDnsSettingService = $locationDnsSettingService;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->cdn->locationDnsSetting->isEmpty()) {
            return ['errorCode' => null];
        }

        $this->locationDnsSettingService->updateToDefaultCdnId($event->cdn, $event->defaultCdn);

        return $this->dnsProviderService->batchEditRecord([
            'record_id' => $event->dnsPodDomainId,
            'change' => 'value',
            'change_to' => $event->defaultCdn->cname,
        ]);
    }
}
