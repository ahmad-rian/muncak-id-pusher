<x-layout.app>
    <x-slot:title>Live Streaming - Muncak.id</x-slot:title>

    <main class="min-h-screen bg-base-200 pt-24 pb-8">
        <div class="container mx-auto px-4">
            <!-- Hero Section -->
            <div class="mb-8 text-center">
                <h1 class="mb-2 font-merriweather text-4xl font-bold text-base-content">
                    Live Streams
                </h1>
                <p class="text-lg text-base-content/70 mb-4">
                    Watch live mountain climbing experiences in real-time
                </p>

                @if ($totalLive > 0)
                    <div class="mt-4">
                        <span class="badge badge-error badge-lg gap-2">
                            <span class="relative flex h-3 w-3">
                                <span
                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex h-3 w-3 rounded-full bg-white"></span>
                            </span>
                            {{ $totalLive }} Live Now
                        </span>
                    </div>
                @endif
            </div>

            <!-- Live Streams Grid -->
            @if ($liveStreams->count() > 0)
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($liveStreams as $stream)
                        <a href="{{ route('live-cam.show', $stream->slug) }}"
                            class="card bg-white shadow-md hover:shadow-lg transition-shadow">
                            <!-- Thumbnail -->
                            <figure class="relative aspect-video bg-black">
                                @if ($stream->thumbnail_url)
                                    <img src="{{ $stream->thumbnail_url }}" alt="{{ $stream->title }}" width="640" height="360"
                                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $loop->first ? 'high' : 'auto' }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-white">
                                        <x-gmdi-videocam-r class="h-16 w-16" />
                                    </div>
                                @endif

                                <!-- LIVE Badge -->
                                <div class="absolute top-3 left-3">
                                    <span class="badge badge-error gap-2 font-semibold">
                                        <span class="relative flex h-2 w-2">
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                                        </span>
                                        LIVE
                                    </span>
                                </div>

                                <!-- Viewer Count -->
                                <div class="absolute top-3 right-3">
                                    <span class="badge badge-neutral gap-1">
                                        <x-gmdi-visibility-r class="h-4 w-4" />
                                        {{ $stream->viewer_count }}
                                    </span>
                                </div>

                                <!-- Quality Badge -->
                                <div class="absolute bottom-3 right-3">
                                    <span class="badge badge-sm">{{ $stream->current_quality }}</span>
                                </div>
                            </figure>

                            <!-- Card Body -->
                            <div class="card-body">
                                <h2 class="card-title line-clamp-1 text-base-content">
                                    {{ $stream->title }}
                                </h2>

                                @if ($stream->hikingTrail)
                                    <div class="flex items-center gap-1 text-sm text-base-content/70">
                                        <x-gmdi-terrain-r class="h-4 w-4" />
                                        <span>{{ $stream->hikingTrail->nama }}
                                            @if($stream->hikingTrail->gunung)
                                                ({{ $stream->hikingTrail->gunung->nama }})
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if ($stream->location)
                                    <div class="flex items-center gap-1 text-sm text-base-content/70">
                                        <x-gmdi-place-r class="h-4 w-4" />
                                        <span>{{ $stream->location }}</span>
                                    </div>
                                @endif

                                <div class="card-actions justify-between items-center mt-2">
                                    <span class="text-xs text-base-content/60">
                                        Started {{ $stream->started_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-20">
                    <x-gmdi-videocam-off-r class="mb-4 h-24 w-24 text-base-content/30" />
                    <h3 class="mb-2 text-2xl font-bold text-base-content/70">No Live Streams</h3>
                    <p class="mb-6 text-base-content/50">There are no active streams at the moment. Check back later!</p>
                </div>
            @endif

            <!-- Trail Conditions Classification Section -->
            @if ($recentClassifications->count() > 0)
                <div class="mt-16"
                    x-data="{
                        searchTerm: '',
                        selectedTrailId: '',
                        modalOpen: false,
                        modalData: null,
                        openModal(data) {
                            this.modalData = data;
                            this.modalOpen = true;
                            document.body.style.overflow = 'hidden';
                        },
                        closeModal() {
                            this.modalOpen = false;
                            this.modalData = null;
                            document.body.style.overflow = '';
                            const video = document.getElementById('modal-video');
                            if (video) { video.pause(); }
                        }
                    }"
                    x-on:keydown.escape.window="closeModal()">

                    <!-- Video Modal -->
                    <div x-show="modalOpen" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-on:click.self="closeModal()">

                        <div class="relative w-full max-w-4xl bg-base-100 rounded-2xl shadow-2xl overflow-hidden"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100">

                            <!-- Close Button -->
                            <button x-on:click="closeModal()" class="absolute top-4 right-4 z-10 btn btn-circle btn-sm bg-black/50 border-none hover:bg-black/70 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Video Player -->
                            <div class="aspect-video bg-black">
                                <video id="modal-video" class="w-full h-full object-contain" x-bind:src="modalData?.video" controls loop autoplay playsinline></video>
                            </div>

                            <!-- Info Panel -->
                            <div class="p-6" x-show="modalData">
                                <h3 class="text-xl font-bold text-base-content mb-3">
                                    <span x-text="modalData?.trail"></span>
                                    <span class="text-base font-normal text-base-content/60" x-show="modalData?.gunung">
                                        (<span x-text="modalData?.gunung"></span>)
                                    </span>
                                </h3>

                                <div class="flex gap-3 flex-wrap mb-4">
                                    <div class="badge badge-lg badge-outline gap-2">
                                        <x-gmdi-wb-sunny-r class="h-4 w-4" />
                                        <span x-text="modalData?.weather"></span>
                                    </div>
                                    <div class="badge badge-lg badge-outline gap-2">
                                        <x-gmdi-group-r class="h-4 w-4" />
                                        <span x-text="modalData?.crowd"></span>
                                    </div>
                                    <div class="badge badge-lg badge-outline gap-2">
                                        <x-gmdi-visibility-r class="h-4 w-4" />
                                        <span x-text="modalData?.visibility"></span>
                                    </div>
                                </div>

                                <p class="text-base-content/70 mb-4" x-text="modalData?.recommendation"></p>

                                <div class="flex items-center gap-2 text-sm text-base-content/50">
                                    <x-gmdi-schedule-r class="h-4 w-4" />
                                    <span x-text="modalData?.timestamp"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="mb-2 font-merriweather text-3xl font-bold text-base-content">
                            Kondisi Jalur Pendakian
                        </h2>
                        <p class="text-base text-base-content/70">
                            Video kondisi jalur terkini dari live streaming (diperbarui setiap 30 menit)
                        </p>
                    </div>

                    <!-- Search and Filter -->
                    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center">
                        <div class="flex-1">
                            <label class="input input-bordered flex items-center gap-2">
                                <x-gmdi-search-r class="h-4 w-4 opacity-70" />
                                <input type="text" x-model="searchTerm" placeholder="Cari jalur pendakian..." class="grow" />
                            </label>
                        </div>
                        <div class="w-full md:w-64">
                            <select x-model="selectedTrailId" class="select select-bordered w-full">
                                <option value="">Semua Jalur</option>
                                @foreach ($availableTrails as $trail)
                                    <option value="{{ $trail->id }}">{{ $trail->nama }}@if($trail->gunung) ({{ $trail->gunung->nama }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="classifications-grid" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($recentClassifications as $classification)
                            @php
                                $cardData = [
                                    'video' => $classification->video_path ? asset('storage/' . $classification->video_path) : null,
                                    'trail' => $classification->hikingTrail->nama ?? 'Unknown Trail',
                                    'gunung' => $classification->hikingTrail->gunung->nama ?? '',
                                    'weather' => $classification->weather_label,
                                    'crowd' => $classification->crowd_label,
                                    'visibility' => $classification->visibility_label,
                                    'recommendation' => $classification->recommendation ?? '',
                                    'timestamp' => $classification->classified_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                                ];
                            @endphp
                            <div class="classification-card card bg-white shadow-md hover:shadow-lg transition-shadow {{ $classification->video_path ? 'cursor-pointer' : '' }}"
                                x-show="(searchTerm === '' || '{{ strtolower(($classification->hikingTrail->nama ?? '') . ' ' . ($classification->hikingTrail->gunung->nama ?? '')) }}'.includes(searchTerm.toLowerCase())) && (selectedTrailId === '' || selectedTrailId === '{{ $classification->hiking_trail_id }}')"
                                x-transition
                                @if($classification->video_path)
                                x-on:click="openModal({{ Js::from($cardData) }})"
                                @endif
                            >
                                <!-- Video/Image Thumbnail -->
                                <figure class="aspect-video bg-black relative group">
                                    @if ($classification->video_path && $classification->image_path)
                                        <img src="{{ asset('storage/' . $classification->image_path) }}"
                                            alt="Trail condition" class="h-full w-full object-cover">
                                        <!-- Play Button -->
                                        <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition-all">
                                            <div class="btn btn-circle btn-lg bg-white/90 border-none hover:bg-white text-primary shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 ml-1">
                                                    <path fill-rule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="absolute bottom-2 left-2">
                                            <span class="badge badge-sm bg-black/70 text-white border-none">5s Video</span>
                                        </div>
                                    @elseif ($classification->image_path)
                                        <img src="{{ asset('storage/' . $classification->image_path) }}"
                                            alt="Trail condition" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-white">
                                            <x-gmdi-terrain-r class="h-16 w-16" />
                                        </div>
                                    @endif
                                </figure>

                                <!-- Classification Info -->
                                <div class="card-body p-4">
                                    <h3 class="font-semibold text-base text-base-content line-clamp-1">
                                        {{ $classification->hikingTrail->nama ?? 'Unknown Trail' }}
                                        @if($classification->hikingTrail && $classification->hikingTrail->gunung)
                                            <span class="text-sm text-base-content/60">({{ $classification->hikingTrail->gunung->nama }})</span>
                                        @endif
                                    </h3>

                                    <div class="flex gap-2 flex-wrap mt-2">
                                        <div class="badge badge-sm badge-outline gap-1">
                                            <x-gmdi-wb-sunny-r class="h-3 w-3" />
                                            {{ $classification->weather_label }}
                                        </div>
                                        <div class="badge badge-sm badge-outline gap-1">
                                            <x-gmdi-group-r class="h-3 w-3" />
                                            {{ $classification->crowd_label }}
                                        </div>
                                        <div class="badge badge-sm badge-outline gap-1">
                                            <x-gmdi-visibility-r class="h-3 w-3" />
                                            {{ $classification->visibility_label }}
                                        </div>
                                    </div>

                                    @if ($classification->recommendation)
                                        <p class="text-sm text-base-content/70 mt-2 line-clamp-2">{{ $classification->recommendation }}</p>
                                    @endif

                                    <div class="mt-3 pt-3 border-t border-base-300">
                                        <div class="flex items-center gap-1 text-xs text-base-content/60">
                                            <x-gmdi-schedule-r class="h-3 w-3" />
                                            <span>{{ $classification->classified_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </main>
</x-layout.app>
