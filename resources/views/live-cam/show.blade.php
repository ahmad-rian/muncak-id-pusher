<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $stream->title }} - Live Stream</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
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
                        <span
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
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
                                    class="absolute inset-0 flex items-center justify-center bg-black/50">
                                    <span class="loading loading-spinner loading-lg text-white"></span>
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
                                            <span
                                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-75"></span>
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-red-500"></span>
                                        </span>
                                        <x-gmdi-visibility-r class="h-4 w-4" />
                                        <span id="viewer-count">{{ $stream->viewer_count }}</span>
                                    </div>
                                </div>

                                <!-- Quality Badge -->
                                <div class="absolute bottom-4 right-4">
                                    <div class="dropdown dropdown-top dropdown-end">
                                        <label tabindex="0"
                                            class="badge badge-lg cursor-pointer bg-black/70 text-white border-0">
                                            <span id="current-quality">{{ $stream->current_quality }}</span>
                                            <x-gmdi-expand-more-r class="h-4 w-4 ml-1" />
                                        </label>
                                        <ul tabindex="0"
                                            class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-32 mb-2">
                                            <li><a onclick="changeQuality('360p')">360p</a></li>
                                            <li><a onclick="changeQuality('720p')">720p</a></li>
                                            <li><a onclick="changeQuality('1080p')">1080p</a></li>
                                        </ul>
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
                                    @if ($stream->mountain)
                                        <div class="flex items-center gap-2">
                                            <x-gmdi-terrain-r class="h-5 w-5" />
                                            <span>{{ $stream->mountain->nama }}</span>
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

                <!-- Chat Section (30%) -->
                <div class="lg:col-span-4">
                    <div class="card bg-white shadow-xl h-[600px] flex flex-col">
                        <!-- Chat Header -->
                        <div class="card-body p-4 border-b border-base-300">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-lg">Live Chat</h3>
                                <div class="badge badge-sm gap-1">
                                    <x-gmdi-people-r class="h-3 w-3" />
                                    <span id="chat-viewer-count">{{ $stream->viewer_count }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Container -->
                        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-2">
                            <div class="text-center text-sm text-base-content/50 mb-4">
                                You joined as <strong>{{ $username }}</strong>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-4 border-t border-base-300">
                            <div class="mb-2 text-xs text-base-content/70">
                                Chatting as <strong>{{ $username }}</strong>
                            </div>
                            <form id="chat-form" class="flex gap-2">
                                <input type="text" id="chat-input" placeholder="Send a message..."
                                    class="input input-bordered flex-1 input-sm" maxlength="200" autocomplete="off" />
                                <button type="submit" class="btn btn-primary btn-sm" id="send-button">
                                    <x-gmdi-send-r class="h-4 w-4" />
                                </button>
                            </form>
                            <div class="mt-1 flex justify-between text-xs text-base-content/50">
                                <span id="char-counter">0/200</span>
                                <span id="throttle-message" class="text-error hidden"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Stream ID
        window.streamId = {{ $stream->id }};

        // Chat username
        window.chatUsername = "{{ $username }}";

        // Configuration for Pusher
        window.pusherConfig = {
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}"
        };
    </script>

    @vite(['resources/js/livecam/viewer-mse.js'])
</body>

</html>
