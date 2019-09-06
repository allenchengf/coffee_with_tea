<?php

namespace App\Events;

use Hiero7\Models\CdnProvider;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Hiero7\Models\Cdn;
class CdnWasBatchEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $changeTo;
    public $change;
    public $recordId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $changeTo, array $cdn, string $change)
    {
        $this->changeTo = $changeTo;
        $this->recordId = implode(',',$cdn);
        $this->change = $change;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [];
    }
}
