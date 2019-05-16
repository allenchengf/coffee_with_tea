<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\CdnWasCreated;

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
            'sub_domain' => $event->domain->cname,
            'value'      => $event->cdn->cname,
            'ttl'        => $event->cdn->ttl,
            'status'     => true
        ]);

    }
}
