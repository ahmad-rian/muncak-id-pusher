<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WebRTC Offer Event - Broadcaster sends offer to viewer
 */
class WebRTCOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $viewerId;
    public $offer;

    public function __construct($streamId, $viewerId, $offer)
    {
        $this->streamId = $streamId;
        $this->viewerId = $viewerId;
        $this->offer = $offer;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('stream.' . $this->streamId);
    }

    public function broadcastAs(): string
    {
        return 'webrtc-offer';
    }

    public function broadcastWith(): array
    {
        return [
            'streamId' => $this->streamId,
            'viewerId' => $this->viewerId,
            'offer' => $this->offer,
        ];
    }
}

/**
 * WebRTC Answer Event - Viewer sends answer to broadcaster
 */
class WebRTCAnswer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $viewerId;
    public $answer;

    public function __construct($streamId, $viewerId, $answer)
    {
        $this->streamId = $streamId;
        $this->viewerId = $viewerId;
        $this->answer = $answer;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('stream.' . $this->streamId);
    }

    public function broadcastAs(): string
    {
        return 'webrtc-answer';
    }

    public function broadcastWith(): array
    {
        return [
            'streamId' => $this->streamId,
            'viewerId' => $this->viewerId,
            'answer' => $this->answer,
        ];
    }
}

/**
 * WebRTC ICE Candidate from Viewer
 */
class WebRTCIceCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $viewerId;
    public $candidate;

    public function __construct($streamId, $viewerId, $candidate)
    {
        $this->streamId = $streamId;
        $this->viewerId = $viewerId;
        $this->candidate = $candidate;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('stream.' . $this->streamId);
    }

    public function broadcastAs(): string
    {
        return 'webrtc-ice-candidate';
    }

    public function broadcastWith(): array
    {
        return [
            'streamId' => $this->streamId,
            'viewerId' => $this->viewerId,
            'candidate' => $this->candidate,
        ];
    }
}

/**
 * WebRTC ICE Candidate from Broadcaster
 */
class WebRTCIceCandidateBroadcaster implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $viewerId;
    public $candidate;

    public function __construct($streamId, $viewerId, $candidate)
    {
        $this->streamId = $streamId;
        $this->viewerId = $viewerId;
        $this->candidate = $candidate;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('stream.' . $this->streamId);
    }

    public function broadcastAs(): string
    {
        return 'webrtc-ice-candidate-broadcaster';
    }

    public function broadcastWith(): array
    {
        return [
            'streamId' => $this->streamId,
            'viewerId' => $this->viewerId,
            'candidate' => $this->candidate,
        ];
    }
}
