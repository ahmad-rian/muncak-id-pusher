<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewerReady implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $viewerId;

    /**
     * Create a new event instance.
     */
    public function __construct($streamId, $viewerId)
    {
        $this->streamId = $streamId;
        $this->viewerId = $viewerId;

        \Log::info('ViewerReady event created', [
            'stream_id' => $streamId,
            'viewer_id' => $viewerId
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('stream.' . $this->streamId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'viewer-ready';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'streamId' => $this->streamId,
            'viewerId' => $this->viewerId,
        ];
    }
}
