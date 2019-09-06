<?php

namespace App\Listeners;

use App\Events\CdnProviderWasDelete;
use Hiero7\Models\Cdn;
use Hiero7\Services\DnsProviderService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteFullDnsPodRecord
{
    protected $dnsProviderService;
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
     * @param  CdnProviderWasDelete  $event
     * @return void
     */
    public function handle($event)
    {
        $recordList = [];
        $cdn = $event->cdn;
        $recordList[] = $cdn->provider_record_id;
        $locationDnsSetting = $event->cdn->locationDnsSetting;
        $collect = collect($locationDnsSetting)->pluck('provider_record_id')->all();
        $recordList = array_merge($recordList, $collect);
        $recordList = array_filter($recordList);

        foreach ($recordList as $key => $value) {
            $deletePodRecord = $this->dnsProviderService->deleteRecord([
                'record_id' => $value,
            ]);
            if(!$result = $this->dnsProviderService->checkAPIOutput($deletePodRecord)){
               return $result;
            }
        }
    }
}
