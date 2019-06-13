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

class CdnWasEdited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $domain;

    public $cdn;

    /**
     * CdnWasEdited constructor.
     *
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn    $cdn
     */
    public function __construct(Domain $domain, Cdn $cdn)
    {
        $this->domain = $domain;

        $this->cdn = $cdn;
    }
}
