<?php

namespace App\Listeners;

use App\Events\CdnWasCreated;
use Hiero7\Services\DnsProviderService;

class CreateDnsPodRecord
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
     * @param \App\Events\CdnWasCreated $event
     *
     * @return mixed
     */
    public function handle(CdnWasCreated $event)
    {

        return $this->dnsProviderService->createRecord([
            'sub_domain' => $event->domain->cname . "." . $event->domain->user_group_id,
            'value' => $event->cdn->cname,
            'ttl' => $event->cdn->cdnProvider->ttl,
            'status' => $event->cdn->default && $event->cdn->cdnProvider->status,
        ]);

    }
}
