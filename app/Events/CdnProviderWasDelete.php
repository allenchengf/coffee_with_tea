<?php

namespace App\Events;

use Hiero7\Models\Cdn;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CdnProviderWasDelete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $cdn;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Cdn $cdn)
    {
        $this->cdn = $cdn;
    }
}
