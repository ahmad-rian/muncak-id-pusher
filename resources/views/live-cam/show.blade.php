<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $stream->title }} - Live Stream</title>

    <!-- Preconnect to improve performance -->
    <link rel="preconnect" href="https://js.pusher.com">
    <link rel="dns-prefetch" href="https://js.pusher.com">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Defer Pusher to not block rendering -->
    <script defer src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>

<body class="bg-base-200" data-stream-id="{{ $stream->id }}">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="navbar bg-base-100 shadow-lg">
            <div class="flex-1">
                <a href="{{ route('live-cam.index') }}" class="btn btn-ghost normal-case text-xl">
                    <x-gmdi-arrow-back-r class="h-5 w-5" />
                    Back to Streams
                </a>
            </div>
            <div class="flex-none">
                <span class="badge badge-error gap-2 mr-4">
                    <span class="relative flex h-2 w-2">
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                    </span>
                    LIVE
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- Video Section (70%) -->
                <div class="lg:col-span-8">
                    <div class="card bg-white shadow-xl">
                        <div class="card-body p-0">
                            <!-- Video Player -->
                            <div class="relative aspect-video bg-black rounded-t-2xl overflow-hidden">
                                <video id="video-player" class="w-full h-full object-contain" autoplay playsinline
                                    muted>
                                </video>


                                <!-- Loading Indicator -->
                                <div id="loading-indicator"
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-black/50">
                                    <span class="loading loading-spinner loading-lg text-white mb-4"></span>
                                    <div class="text-white text-sm mb-2" id="loading-text">Connecting to stream...</div>
                                    <div class="w-64 bg-gray-700 rounded-full h-2 overflow-hidden"
                                        id="loading-progress-container" style="display: none;">
                                        <div class="bg-primary h-full transition-all duration-300"
                                            id="loading-progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>


                                <!-- Offline Placeholder -->
                                <div id="offline-placeholder"
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-black text-white hidden">
                                    <x-gmdi-videocam-off-r class="h-24 w-24 mb-4 opacity-50" />
                                    <h3 class="text-2xl font-bold mb-2">Stream has ended</h3>
                                    <p class="text-base-content/70">Thank you for watching!</p>
                                </div>

                                <!-- Viewer Count -->
                                <div class="absolute top-4 left-4">
                                    <div class="badge badge-lg gap-2 bg-black/70 text-white border-0">
                                        <span class="relative flex h-3 w-3">
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-red-500"></span>
                                        </span>
                                        <x-gmdi-visibility-r class="h-4 w-4" />
                                        <span id="viewer-count">{{ $stream->viewer_count }}</span>
                                    </div>
                                </div>


                                <!-- Adaptive Quality Badge -->
                                <div class="absolute bottom-4 right-4">
                                    <div class="badge badge-lg bg-black/70 text-white border-0 gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span>Auto Quality</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stream Info -->
                            <div class="p-6">
                                <h1 class="text-2xl font-bold mb-2">{{ $stream->title }}</h1>
                                @if ($stream->description)
                                    <p class="text-base-content/70 mb-4">{{ $stream->description }}</p>
                                @endif

                                <div class="flex flex-wrap gap-4">
                                    @if ($stream->hikingTrail)
                                        <div class="flex items-center gap-2">
                                            <x-gmdi-terrain-r class="h-5 w-5 text-primary" />
                                            <span>{{ $stream->hikingTrail->nama }}
                                                @if($stream->hikingTrail->gunung)
                                                    ({{ $stream->hikingTrail->gunung->nama }})
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    @if ($stream->location)
                                        <div class="flex items-center gap-2">
                                            <x-gmdi-place-r class="h-5 w-5" />
                                            <span>{{ $stream->location }}</span>
                                        </div>
                                    @endif
                                    @if ($stream->started_at)
                                        <div class="flex items-center gap-2">
                                            <x-gmdi-schedule-r class="h-5 w-5" />
                                            <span>Started {{ $stream->started_at->diffForHumans() }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <x-gmdi-schedule-r class="h-5 w-5" />
                                            <span>Created {{ $stream->created_at->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (30%) -->
                <div class="lg:col-span-4 space-y-4">

                    <!-- Trail Classification Display -->
                    <div id="classification-display" class="card bg-white shadow-xl hidden">
                        <div class="card-body p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-sm flex items-center gap-2">
                                    <x-gmdi-analytics-r class="h-4 w-4" />
                                    Kondisi Jalur
                                </h3>
                                <span id="classification-time" class="text-xs text-base-content/60">Memuat...</span>
                            </div>

                            <div class="space-y-2">
                                <!-- Weather -->
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Cuaca</span>
                                    <span id="classification-weather" class="text-sm font-semibold">-</span>
                                </div>

                                <!-- Crowd -->
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Kepadatan</span>
                                    <span id="classification-crowd" class="text-sm font-semibold">-</span>
                                </div>

                                <!-- Visibility -->
                                <div class="flex items-center justify-between py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Visibilitas</span>
                                    <span id="classification-visibility" class="text-sm font-semibold">-</span>
                                </div>

                                <!-- Recommendation -->
                                <div class="mt-3 p-2 bg-base-200 rounded-lg">
                                    <p id="classification-recommendation" class="text-xs text-center">
                                        💡 Data akan diperbarui setiap 5 menit
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Chat Card -->
                    <div class="card bg-gradient-to-br from-base-100 to-base-200 shadow-2xl flex-1 flex flex-col overflow-hidden" style="min-height: 400px;">
                        <!-- Chat Header with Gradient -->
                        <div class="bg-gradient-to-r from-primary to-secondary p-4">
                            <div class="flex items-center justify-between text-primary-content">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <h3 class="font-bold text-lg">Live Chat</h3>
                                </div>
                                <div class="badge badge-lg bg-white/20 border-0 gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span id="chat-viewer-count" class="font-semibold">{{ $stream->viewer_count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Container with Custom Scrollbar -->
                        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-base-100" style="scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.2) transparent;">
                            <div class="text-center py-8">
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-base-200 rounded-full text-sm text-base-content/70">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>You joined as <strong class="text-primary">{{ $username }}</strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Modern Chat Input -->
                        <div class="p-4 bg-base-200 border-t border-base-300">
                            <form id="chat-form" class="space-y-2">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" id="chat-input" placeholder="Type your message..." 
                                            class="input input-bordered w-full pr-12 focus:outline-none focus:ring-2 focus:ring-primary" 
                                            maxlength="200" autocomplete="off" />
                                        <span id="char-counter" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-base-content/40">0/200</span>
                                    </div>
                                    <button type="submit" class="btn btn-primary gap-2" id="send-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                        <span class="hidden sm:inline">Send</span>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-base-content/50">Chatting as <strong class="text-primary">{{ $username }}</strong></span>
                                    <span id="throttle-message" class="text-error font-medium hidden"></span>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <script>
        // Stream ID and Slug
        window.streamId = {{ $stream->id }};
        window.streamSlug = "{{ $stream->slug }}";

        // Chat username
        window.chatUsername = "{{ $username }}";

        // Configuration for Pusher
        window.pusherConfig = {
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}"
        };

        // Initialize Pusher when available (deferred loading)
        function initPusher() {
            if (typeof Pusher !== 'undefined') {
                window.pusher = new Pusher(window.pusherConfig.key, {
                    cluster: window.pusherConfig.cluster,
                    forceTLS: true
                });
            } else {
                // Retry after 100ms if Pusher not loaded yet
                setTimeout(initPusher, 100);
            }
        }

        // Start initialization
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPusher);
        } else {
            initPusher();
        }
    </script>

    {{-- LiveKit Viewer (SFU - ultra-low latency) --}}
    @vite(['resources/js/livecam/viewer-livekit.js', 'resources/js/livecam/trail-classifier.js'])
</body>

</html>