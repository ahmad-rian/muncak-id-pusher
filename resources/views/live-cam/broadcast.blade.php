<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Broadcast - {{ $stream->title }}</title>
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
                    Broadcaster Dashboard
                </a>
            </div>
            <div class="flex-none">
                <span id="stream-status" class="badge gap-2 mr-4">
                    <span class="h-2 w-2 rounded-full bg-base-content"></span>
                    OFFLINE
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- Video Section -->
                <div class="lg:col-span-8">
                    <div class="card bg-white shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title mb-4">Camera Preview</h2>

                            <!-- Camera Preview -->
                            <div class="relative aspect-video bg-black rounded-lg overflow-hidden mb-4">
                                <video id="camera-preview" class="w-full h-full object-contain" autoplay playsinline
                                    muted>
                                </video>

                                <!-- No Camera Message -->
                                <div id="no-camera"
                                    class="absolute inset-0 flex flex-col items-center justify-center text-white">
                                    <x-gmdi-videocam-off-r class="h-24 w-24 mb-4 opacity-50" />
                                    <p class="text-lg">No camera detected</p>
                                </div>

                                <!-- Camera Controls (Overlay) -->
                                <div class="absolute bottom-4 right-4 flex gap-2">
                                    <button id="switch-camera"
                                        class="btn btn-sm btn-circle btn-ghost bg-black/50 text-white hover:bg-black/70"
                                        title="Switch Camera Front/Back">
                                        <x-gmdi-cameraswitch-r class="h-5 w-5" />
                                    </button>
                                    <button id="mirror-camera"
                                        class="btn btn-sm btn-circle btn-ghost bg-black/50 text-white hover:bg-black/70"
                                        title="Mirror Camera">
                                        <x-gmdi-flip-r class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Device Selectors -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Camera</span>
                                    </label>
                                    <select id="camera-select" class="select select-bordered">
                                        <option disabled selected>Select camera...</option>
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Microphone</span>
                                    </label>
                                    <select id="microphone-select" class="select select-bordered">
                                        <option disabled selected>Select microphone...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Quality Selector -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold">Stream Quality</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="quality" value="360p"
                                            class="radio radio-primary" />
                                        <span class="label-text">360p</span>
                                    </label>
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="quality" value="720p" class="radio radio-primary"
                                            checked />
                                        <span class="label-text">720p</span>
                                    </label>
                                    <label class="label cursor-pointer gap-2">
                                        <input type="radio" name="quality" value="1080p"
                                            class="radio radio-primary" />
                                        <span class="label-text">1080p</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Start/Stop Button -->
                            <div class="flex gap-4">
                                <button id="start-button" class="btn btn-success flex-1 btn-lg">
                                    <x-gmdi-play-arrow-r class="h-6 w-6" />
                                    Start Streaming
                                </button>
                                <button id="stop-button" class="btn btn-error flex-1 btn-lg hidden">
                                    <x-gmdi-stop-r class="h-6 w-6" />
                                    Stop Streaming
                                </button>
                            </div>

                            <!-- Warning -->
                            <div class="alert alert-warning mt-4">
                                <x-gmdi-warning-r class="h-5 w-5" />
                                <div>
                                    <h4 class="font-bold">Important!</h4>
                                    <p class="text-sm">Ensure you have a stable internet connection. Minimum
                                        requirement: 5 Mbps upload speed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Chat Monitor -->
                <div class="lg:col-span-4 space-y-4">

                    <!-- Stats Panel -->
                    <div class="card bg-white shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Stream Statistics</h3>

                            <div class="stats stats-vertical shadow">
                                <div class="stat">
                                    <div class="stat-title">Current Viewers</div>
                                    <div class="stat-value text-primary" id="viewer-count">0</div>
                                    <div class="stat-desc">Watching now</div>
                                </div>

                                <div class="stat">
                                    <div class="stat-title">Stream Duration</div>
                                    <div class="stat-value text-secondary" id="stream-duration">00:00:00</div>
                                    <div class="stat-desc">Hours:Minutes:Seconds</div>
                                </div>

                                <div class="stat">
                                    <div class="stat-title">Current Quality</div>
                                    <div class="stat-value text-accent" id="current-quality">720p</div>
                                    <div class="stat-desc">Streaming quality</div>
                                </div>
                            </div>

                            <!-- Connection Status -->
                            <div class="mt-4">
                                <label class="label">
                                    <span class="label-text font-semibold">Connection Status</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <div id="connection-indicator" class="badge badge-lg gap-2">
                                        <span class="h-2 w-2 rounded-full bg-base-content"></span>
                                        <span id="connection-status">Disconnected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Monitor -->
                    <div class="card bg-white shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Chat Monitor</h3>
                            <div id="chat-monitor"
                                class="h-64 overflow-y-auto border border-base-300 rounded-lg p-2 space-y-2">
                                <div class="text-center text-sm text-base-content/50">
                                    Chat messages will appear here...
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Stream Info -->
            <div class="card bg-white shadow-xl mt-4">
                <div class="card-body">
                    <h3 class="card-title">Stream Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <div>
                            <p class="font-semibold">Title:</p>
                            <p>{{ $stream->title }}</p>
                        </div>
                        @if ($stream->hikingTrail)
                            <div>
                                <p class="font-semibold">Jalur Pendakian:</p>
                                <p>{{ $stream->hikingTrail->nama }}</p>
                                @if ($stream->hikingTrail->gunung)
                                    <p class="text-sm text-base-content/70">Gunung:
                                        {{ $stream->hikingTrail->gunung->nama }}</p>
                                @endif
                            </div>
                        @endif
                        @if ($stream->location)
                            <div>
                                <p class="font-semibold">Location:</p>
                                <p>{{ $stream->location }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold">Stream Key:</p>
                            <p class="font-mono text-sm">{{ $stream->stream_key }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration for broadcaster
        window.streamId = {{ $stream->id }};
        window.streamSlug = "{{ $stream->slug }}";
        window.pusherConfig = {
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}"
        };
    </script>

    @vite(['resources/js/livecam/broadcaster-livekit.js'])
</body>

</html>
