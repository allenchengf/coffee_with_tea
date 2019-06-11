<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;

class EditDnsPodRecord
{
    protected $dnsProviderService;

    /**
     * CreateDnsPodRecord constructor.
     *
     * @param \Hiero7\Services\DnsProviderService $dnsProviderService
     */
    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
    }

    /**
     * @param $event
     *
     * @return mixed
     */
    public function handle($event)
    {

        return $this->dnsProviderService->editRecord([
            'record_id' => $event->cdn->dns_provider_id,
            'sub_domain' => $event->domain->cname,
            'record_type' => "CNAME",
            'record_line' => "默认",
            'value' => $event->cdn->cname,
            'ttl' => $event->cdn->cdnProvider->ttl,
            'status' => $event->cdn->default && $event->cdn->cdnProvider->status,
        ]);
    }
}
