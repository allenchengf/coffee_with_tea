<?php

namespace App\Events;

use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CdnWasCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $domain;

    public $cdn;

    /**
     * CdnWasCreated constructor.
     *
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn    $cdn
     */
    public function __construct(Domain $domain, Cdn $cdn)
    {
        $this->domain = $domain;

        $this->cdn = $cdn;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [];
        //return new PrivateChannel('channel-name');
    }
}
