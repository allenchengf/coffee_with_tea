<?php

namespace App\Listeners;

use Hiero7\Services\DnsProviderService;

class EditDnsPodRecord
{
    protected $dnsProviderService;

    /**
     * EditDnsPodRecord constructor.
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
        $data = [
            'record_id'   => $event->cdn->provider_record_id,
            'sub_domain'  => $event->domain->cname,
            'record_type' => "CNAME",
            'record_line' => "默认",
            'value'       => $event->cdn->cname,
            'ttl'         => $event->cdn->cdnProvider->ttl,
            'status'      => $event->cdn->cdnProvider->status,
        ];

        $response = $this->dnsProviderService->editRecord($data);

        return $this->dnsProviderService->checkAPIOutput($response);
    }
}
