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
                                    <img src="{{ $stream->thumbnail_url }}" alt="{{ $stream->title }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-white">
                                        <x-gmdi-videocam-r class="h-16 w-16" />
                                    </div>
                                @endif

                                <!-- LIVE Badge -->
                                <div class="absolute top-3 left-3">
                                    <span class="badge badge-error gap-2 font-semibold">
                                        <span class="relative flex h-2 w-2">
                                            <span
                                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
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
                    <p class="mb-6 text-base-content/50">There are no active streams at the moment. Check back later!
                    </p>
                </div>
            @endif

            <!-- Trail Conditions Classification Section -->
            @if ($recentClassifications->count() > 0)
                <div class="mt-16" x-data="{
                    searchTerm: '',
                    selectedTrailId: '',
                    classifications: {{ Js::from($recentClassifications->map(function($c) {
                        return [
                            'id' => $c->id,
                            'trail_id' => $c->hiking_trail_id,
                            'trail_name' => ($c->hikingTrail->nama ?? '') . ' ' . ($c->hikingTrail->gunung->nama ?? '')
                        ];
                    })) }}
                }">
                    <div class="mb-6">
                        <h2 class="mb-2 font-merriweather text-3xl font-bold text-base-content">
                            Kondisi Jalur Pendakian
                        </h2>
                        <p class="text-base text-base-content/70">
                            Laporan kondisi jalur terkini dari live streaming
                        </p>
                    </div>

                    <!-- Search and Filter -->
                    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center">
                        <!-- Search Bar -->
                        <div class="flex-1">
                            <label class="input input-bordered flex items-center gap-2">
                                <x-gmdi-search-r class="h-4 w-4 opacity-70" />
                                <input type="text"
                                    x-model="searchTerm"
                                    placeholder="Cari jalur pendakian..."
                                    class="grow" />
                            </label>
                        </div>

                        <!-- Filter Dropdown -->
                        <div class="w-full md:w-64">
                            <select x-model="selectedTrailId" class="select select-bordered w-full">
                                <option value="">Semua Jalur</option>
                                @foreach ($availableTrails as $trail)
                                    <option value="{{ $trail->id }}">
                                        {{ $trail->nama }}
                                        @if($trail->gunung)
                                            ({{ $trail->gunung->nama }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="classifications-grid" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($recentClassifications as $classification)
                            <div class="classification-card card bg-white shadow-md hover:shadow-lg transition-shadow"
                                x-show="(searchTerm === '' || '{{ strtolower(($classification->hikingTrail->nama ?? '') . ' ' . ($classification->hikingTrail->gunung->nama ?? '')) }}'.includes(searchTerm.toLowerCase())) && (selectedTrailId === '' || selectedTrailId === '{{ $classification->hiking_trail_id }}')"
                                x-transition>
                                <!-- Classification Image -->
                                <figure class="aspect-video bg-black">
                                    @if ($classification->image_path)
                                        <img src="{{ asset('storage/' . $classification->image_path) }}"
                                            alt="Trail condition at {{ $classification->hikingTrail->nama ?? 'Unknown trail' }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-white">
                                            <x-gmdi-terrain-r class="h-16 w-16" />
                                        </div>
                                    @endif
                                </figure>

                                <!-- Classification Info -->
                                <div class="card-body p-4">
                                    <!-- Trail Name -->
                                    <h3 class="font-semibold text-base text-base-content line-clamp-1">
                                        @if ($classification->hikingTrail)
                                            {{ $classification->hikingTrail->nama }}
                                            @if($classification->hikingTrail->gunung)
                                                <span class="text-sm text-base-content/60">({{ $classification->hikingTrail->gunung->nama }})</span>
                                            @endif
                                        @elseif ($classification->liveStream && $classification->liveStream->hikingTrail)
                                            {{ $classification->liveStream->hikingTrail->nama }}
                                            @if($classification->liveStream->hikingTrail->gunung)
                                                <span class="text-sm text-base-content/60">({{ $classification->liveStream->hikingTrail->gunung->nama }})</span>
                                            @endif
                                        @else
                                            <span class="text-base-content/60">Unknown Trail</span>
                                        @endif
                                    </h3>

                                    <!-- Conditions Badges -->
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

                                    <!-- Recommendation -->
                                    @if ($classification->recommendation)
                                        <p class="text-sm text-base-content/70 mt-2 line-clamp-2">
                                            {{ $classification->recommendation }}
                                        </p>
                                    @endif

                                    <!-- Timestamp -->
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
