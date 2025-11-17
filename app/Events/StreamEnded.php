<?php

namespace App\Events;

use App\Models\LiveStream;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStream;

    public function __construct(LiveStream $liveStream)
    {
        $this->liveStream = $liveStream;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('stream.' . $this->liveStream->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stream-ended';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'status' => $this->liveStream->status,
            'ended_at' => $this->liveStream->ended_at,
        ];
    }
}
