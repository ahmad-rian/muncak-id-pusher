<?php

use Illuminate\Support\Facades\Broadcast;

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
    // Debug logging
    \Log::info('Broadcast auth attempt', [
        'user_id' => $user ? $user->id : null,
        'stream_id' => $streamId
    ]);

    if (!$user) {
        \Log::warning('No user authenticated for broadcast channel');
        return false;
    }

    $stream = \App\Models\LiveStream::find($streamId);

    if (!$stream) {
        \Log::warning('Stream not found', ['stream_id' => $streamId]);
        return false;
    }

    $authorized = $stream->broadcaster_id === $user->id;
    \Log::info('Broadcast authorization result', [
        'authorized' => $authorized,
        'broadcaster_id' => $stream->broadcaster_id,
        'user_id' => $user->id
    ]);

    return $authorized;
});

// Private signaling channels for WebRTC
Broadcast::channel('private-signals.{streamId}.{viewerId}', function ($user, $streamId, $viewerId) {
    // Allow authenticated broadcasters or the specific viewer
    return true; // Simplified for demo - add proper auth in production
});
