<?php

namespace App\Listeners;

use App\Events\CdnWasBatchEdited;
use Hiero7\Services\DnsProviderService;

class BatchEditDnsPodRecord
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DnsProviderService $dnsProviderService)
    {
        $this->dnsProviderService = $dnsProviderService;
    }

    /**
     * Handle the event.
     *
     * @param  CdnWasBatchEdited  $event
     * @return void
     */
    public function handle($event)
    {
        return $this->dnsProviderService->batchEditRecord([
            'record_id' => $event->recordId,
            'change' => $event->change,
            'change_to' => $event->changeTo
        ]);
    }
}
