<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LiveKit Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your LiveKit server connection details here.
    | You can use LiveKit Cloud or self-host your own server.
    |
    */

    'url' => env('LIVEKIT_URL', 'ws://localhost:7880'),

    'api_key' => env('LIVEKIT_API_KEY', 'devkey'),

    'api_secret' => env('LIVEKIT_API_SECRET', 'secret'),

    /*
    |--------------------------------------------------------------------------
    | Token Settings
    |--------------------------------------------------------------------------
    */

    'token_ttl' => env('LIVEKIT_TOKEN_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Room Settings
    |--------------------------------------------------------------------------
    */

    'room_prefix' => env('LIVEKIT_ROOM_PREFIX', 'stream'),

    'max_participants' => env('LIVEKIT_MAX_PARTICIPANTS', 1000),
];
