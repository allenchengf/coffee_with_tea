<?php

namespace App\Events;

use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CdnWasDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $defaultCdn, $cdn, $dnsPodDomainId;

    /**
     * CdnWasCreated constructor.
     *
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn    $cdn
     */
    public function __construct(Cdn $defaultCdn, Cdn $cdn)
    {
        $this->defaultCdn = $defaultCdn;

        $this->cdn = $cdn;

        $this->dnsPodDomainId = implode(',', $cdn->getlocationDnsSettingDomainId($cdn->id)->toArray());

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
