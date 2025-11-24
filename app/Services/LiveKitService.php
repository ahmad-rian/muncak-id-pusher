<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Models\LiveStream;
use Illuminate\Support\Facades\Log;

class LiveKitService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $url;

    public function __construct()
    {
        $this->apiKey = config('livekit.api_key');
        $this->apiSecret = config('livekit.api_secret');
        $this->url = config('livekit.url');
    }

    /**
     * Generate access token for broadcaster
     */
    public function generateBroadcasterToken(LiveStream $stream, $userId): string
    {
        $roomName = $this->getRoomName($stream);

        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity("broadcaster-{$userId}")
            ->setTtl(config('livekit.token_ttl'));

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName)
            ->setCanPublish(true)
            ->setCanSubscribe(true)
            ->setCanPublishData(true);

        $token = (new AccessToken($this->apiKey, $this->apiSecret))
            ->init($tokenOptions)
            ->setGrant($videoGrant);

        Log::info('Generated broadcaster token', [
            'stream_id' => $stream->id,
            'room' => $roomName,
            'user_id' => $userId
        ]);

        return $token->toJwt();
    }

    /**
     * Generate access token for viewer
     */
    public function generateViewerToken(LiveStream $stream, $userId = null): string
    {
        $roomName = $this->getRoomName($stream);
        $identity = $userId ? "viewer-{$userId}" : "guest-" . uniqid();

        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setTtl(config('livekit.token_ttl'));

        $videoGrant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName)
            ->setCanPublish(false) // Viewers can't publish
            ->setCanSubscribe(true) // But can watch
            ->setCanPublishData(true); // Can send chat

        $token = (new AccessToken($this->apiKey, $this->apiSecret))
            ->init($tokenOptions)
            ->setGrant($videoGrant);

        Log::info('Generated viewer token', [
            'stream_id' => $stream->id,
            'room' => $roomName,
            'identity' => $identity
        ]);

        return $token->toJwt();
    }

    /**
     * Get room name for stream
     */
    public function getRoomName(LiveStream $stream): string
    {
        $prefix = config('livekit.room_prefix');
        return "{$prefix}-{$stream->id}";
    }

    /**
     * Get LiveKit server URL
     */
    public function getServerUrl(): string
    {
        return $this->url;
    }
}
