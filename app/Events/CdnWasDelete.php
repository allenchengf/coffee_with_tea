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

    public $cdn;

    /**
     * CdnWasCreated constructor.
     *
     * @param \Hiero7\Models\Domain $domain
     * @param \Hiero7\Models\Cdn    $cdn
     */
    public function __construct(Cdn $cdn)
    {
        $this->cdn = $cdn;
    }
}
