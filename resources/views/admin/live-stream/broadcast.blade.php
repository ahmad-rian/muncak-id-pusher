<x-layout.admin>
    <div class="breadcrumbs text-sm text-base-content/70">
        <ul>
            <li><a href="{{ route('admin.live-stream.index') }}">Live Streaming</a></li>
            <li>Broadcast</li>
        </ul>
    </div>

    <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
        <div>
            <p class="text-2xl font-semibold">Broadcaster Dashboard</p>
            <p class="text-sm text-base-content/70 mt-1">{{ $stream->title }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span id="stream-status" class="badge gap-2">
                <span class="h-2 w-2 rounded-full bg-base-content"></span>
                OFFLINE
            </span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">

        <!-- Video Section -->
        <div class="lg:col-span-8">
            <div class="rounded-lg border border-base-300 bg-base-100">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold">Camera Preview</h2>

                    <!-- Camera Preview -->
                    <div class="relative mb-4 aspect-video overflow-hidden rounded-lg bg-black">
                        <video id="camera-preview" class="h-full w-full object-contain" autoplay playsinline muted>
                        </video>

                        <!-- No Camera Message -->
                        <div id="no-camera"
                            class="absolute inset-0 flex flex-col items-center justify-center text-white">
                            <x-gmdi-videocam-off-r class="mb-4 h-24 w-24 opacity-50" />
                            <p class="text-lg">No camera detected</p>
                        </div>

                        <!-- Mirror Camera Button (Overlay) -->
                        <div class="absolute bottom-4 right-4">
                            <button id="mirror-camera" class="btn btn-sm btn-circle btn-ghost bg-black/50 text-white hover:bg-black/70" title="Mirror Camera">
                                <x-gmdi-flip-r class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Device Selectors -->
                    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
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
                                <input type="radio" name="quality" value="360p" class="radio radio-primary" />
                                <span class="label-text">360p</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="quality" value="720p" class="radio radio-primary"
                                    checked />
                                <span class="label-text">720p</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="quality" value="1080p" class="radio radio-primary" />
                                <span class="label-text">1080p</span>
                            </label>
                        </div>
                    </div>

                    <!-- Start/Stop Button -->
                    <div class="flex gap-4">
                        <button id="start-button" class="btn btn-success btn-lg flex-1">
                            <x-gmdi-play-arrow-r class="h-6 w-6" />
                            Start Streaming
                        </button>
                        <button id="stop-button" class="btn btn-error btn-lg hidden flex-1">
                            <x-gmdi-stop-r class="h-6 w-6" />
                            Stop Streaming
                        </button>
                    </div>

                    <!-- Warning -->
                    <div class="alert alert-warning mt-4">
                        <x-gmdi-warning-r class="h-5 w-5" />
                        <div>
                            <h4 class="font-bold">Important!</h4>
                            <p class="text-sm">Ensure you have a stable internet connection. Minimum requirement: 5
                                Mbps upload speed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats & Chat Monitor -->
        <div class="space-y-6 lg:col-span-4">

            <!-- Stats Panel -->
            <div class="rounded-lg border border-base-300 bg-base-100">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Stream Statistics</h3>

                    <div class="space-y-4">
                        <div class="rounded-lg border border-base-300 p-4">
                            <div class="text-sm text-base-content/70">Current Viewers</div>
                            <div class="text-primary text-3xl font-bold" id="viewer-count">0</div>
                            <div class="text-xs text-base-content/60">Watching now</div>
                        </div>

                        <div class="rounded-lg border border-base-300 p-4">
                            <div class="text-sm text-base-content/70">Stream Duration</div>
                            <div class="text-secondary text-3xl font-bold" id="stream-duration">00:00:00</div>
                            <div class="text-xs text-base-content/60">Hours:Minutes:Seconds</div>
                        </div>

                        <div class="rounded-lg border border-base-300 p-4">
                            <div class="text-sm text-base-content/70">Current Quality</div>
                            <div class="text-accent text-3xl font-bold" id="current-quality">720p</div>
                            <div class="text-xs text-base-content/60">Streaming quality</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Chat Monitor -->
            <div class="rounded-lg border border-base-300 bg-base-100">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Chat Monitor</h3>

                    <div id="chat-monitor"
                        class="h-64 space-y-2 overflow-y-auto rounded-lg border border-base-300 bg-base-200 p-4">
                        <div class="text-center text-sm text-base-content/50">
                            No messages yet
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stream Info -->
            <div class="rounded-lg border border-base-300 bg-base-100">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold">Stream Information</h3>

                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-semibold">Stream ID:</span>
                            <code class="ml-2 rounded bg-base-200 px-2 py-1">{{ $stream->id }}</code>
                        </div>
                        <div>
                            <span class="font-semibold">Stream Key:</span>
                            <code
                                class="ml-2 rounded bg-base-200 px-2 py-1">{{ substr($stream->stream_key, 0, 20) }}...</code>
                        </div>
                        @if ($stream->hikingTrail)
                            <div>
                                <span class="font-semibold">Jalur Pendakian:</span>
                                <span class="ml-2">{{ $stream->hikingTrail->nama }}</span>
                                @if($stream->hikingTrail->gunung)
                                    <span class="text-base-content/70">({{ $stream->hikingTrail->gunung->nama }})</span>
                                @endif
                            </div>
                        @endif
                        <div>
                            <span class="font-semibold">Created:</span>
                            <span class="ml-2">{{ $stream->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('live-cam.show', $stream->id) }}" target="_blank"
                            class="btn btn-neutral btn-sm btn-block">
                            <x-gmdi-open-in-new-r class="h-4 w-4" />
                            Open Viewer Page
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <x-slot:js>
        <script>
            // Configuration for broadcaster
            window.streamId = {{ $stream->id }};
            window.streamSlug = "{{ $stream->slug }}";
            window.pusherConfig = {
                key: "{{ config('broadcasting.connections.pusher.key') }}",
                cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}"
            };
        </script>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        @vite(['resources/js/livecam/broadcaster-mse.js'])
    </x-slot:js>
</x-layout.admin>
