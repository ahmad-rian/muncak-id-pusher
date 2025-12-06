<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrientationChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $orientation;
    public $width;
    public $height;

    /**
     * Create a new event instance.
     */
    public function __construct($streamId, $orientation, $width, $height)
    {
        $this->streamId = $streamId;
        $this->orientation = $orientation;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('stream.' . $this->streamId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'App\\Events\\OrientationChanged';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'orientation' => $this->orientation,
            'width' => $this->width,
            'height' => $this->height,
            'stream_id' => $this->streamId
        ];
    }
}
