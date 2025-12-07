<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public stream channel (no auth required - anyone can listen)
// Note: We use public channel instead of presence because presence requires authentication
// Viewer tracking is handled manually via API calls
Broadcast::channel('stream.{streamId}', function () {
    // Public channel - no authentication required
    return true;
});

// Private broadcaster channel (auth required)
Broadcast::channel('private-broadcast.{streamId}', function ($user, $streamId) {
    if (!$user) {
        return false;
    }

    // Cache stream lookup for 5 minutes to reduce DB queries
    $stream = Cache::remember("stream_auth_{$streamId}", 300, function() use ($streamId) {
        return \App\Models\LiveStream::find($streamId);
    });

    if (!$stream) {
        return false;
    }

    return $stream->broadcaster_id === $user->id;
});

// Private signaling channels for WebRTC
Broadcast::channel('private-signals.{streamId}.{viewerId}', function ($user, $streamId, $viewerId) {
    // Allow authenticated broadcasters or the specific viewer
    return true; // Simplified for demo - add proper auth in production
});
