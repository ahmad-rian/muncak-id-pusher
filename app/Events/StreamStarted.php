<?php

namespace App\Events;

use App\Models\LiveStream;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamStarted implements ShouldBroadcast
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
        return 'stream-started';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'title' => $this->liveStream->title,
            'status' => $this->liveStream->status,
            'started_at' => $this->liveStream->started_at,
        ];
    }
}
