<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QualityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $quality;

    public function __construct($streamId, $quality)
    {
        $this->streamId = $streamId;
        $this->quality = $quality;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('stream.' . $this->streamId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'quality' => $this->quality,
        ];
    }
}
