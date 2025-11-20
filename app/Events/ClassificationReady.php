<?php

namespace App\Events;

use App\Models\TrailClassification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassificationReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $classification;

    public function __construct(TrailClassification $classification)
    {
        $this->classification = $classification;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('stream.' . $this->classification->live_stream_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'classification-ready';
    }

    public function broadcastWith(): array
    {
        return [
            'classification_id' => $this->classification->id,
            'weather' => $this->classification->weather,
            'crowd' => $this->classification->crowd,
            'visibility' => $this->classification->visibility,
            'weather_label' => $this->classification->weather_label,
            'crowd_label' => $this->classification->crowd_label,
            'visibility_label' => $this->classification->visibility_label,
            'recommendation' => $this->classification->recommendation,
            'classified_at' => $this->classification->classified_at->format('H:i:s'),
        ];
    }
}
