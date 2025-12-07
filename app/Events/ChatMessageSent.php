<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $username;
    public $message;
    public $timestamp;

    public function __construct($streamId, $username, $message)
    {
        $this->streamId = $streamId;
        $this->username = $username;
        $this->message = $message;
        $this->timestamp = now();
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
            'username' => $this->username,
            'message' => $this->message,
            'timestamp' => $this->timestamp->toIso8601String(),
        ];
    }
}
