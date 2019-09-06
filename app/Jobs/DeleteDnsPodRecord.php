<?php

namespace App\Jobs;

use Exception;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Services\DnsProviderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteDnsPodRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DnsProviderService $dnsProviderService, LocationDnsSetting $locationDnsSetting)
    {
        $cdnIdIsNull = $locationDnsSetting->where('cdn_id', null)->get();
        $deleteError = false;
        $errorArray = [];

        foreach ($cdnIdIsNull as $key => $value) {
            $response = $dnsProviderService->deleteRecord([
                'record_id' => $value->provider_record_id,
            ]);

            if ($dnsProviderService->checkAPIOutput($response)) {
                $value->delete();
            } else {
                $deleteError = true;
                $errorArray[] = $value->only(['id', 'provider_record_id', 'domain_id']);
            }
        }

        if ($deleteError) {
            throw new Exception("Delete Error " . json_encode($errorArray), 409);
        }
    }

    public function failed(Exception $exception = null)
    {
        // 給用戶發送失敗的通知等等...
    }
}
