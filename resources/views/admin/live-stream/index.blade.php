<x-layout.admin>
    <div class="breadcrumbs text-sm text-base-content/70">
        <ul>
            <li><a href="{{ route('admin.live-stream.index') }}">Live Streaming</a></li>
            <li>List</li>
        </ul>
    </div>

    <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
        <p class="text-2xl font-semibold">Live Streaming</p>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.live-stream.create') }}">
            <x-gmdi-add-r class="size-4" />
            Buat Stream Baru
        </a>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($streams as $stream)
            <div class="card bg-base-100 shadow-md">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <h2 class="card-title text-lg">{{ $stream->title }}</h2>
                        @if ($stream->status === 'live')
                            <span class="badge badge-error badge-sm gap-1">
                                <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                                LIVE
                            </span>
                        @else
                            <span class="badge badge-ghost badge-sm">{{ strtoupper($stream->status) }}</span>
                        @endif
                    </div>

                    <p class="text-sm text-base-content/70">{{ Str::limit($stream->description, 100) }}</p>

                    <div class="mt-2 flex flex-wrap gap-2 text-sm">
                        <div class="badge badge-outline gap-1">
                            <x-gmdi-visibility-r class="size-4" />
                            {{ $stream->viewer_count ?? 0 }} viewers
                        </div>
                        <div class="badge badge-outline gap-1">
                            <x-gmdi-hd-r class="size-4" />
                            {{ $stream->current_quality ?? '720p' }}
                        </div>
                        @if ($stream->hikingTrail)
                            <div class="badge badge-outline gap-1">
                                <x-gmdi-terrain-r class="size-4" />
                                {{ $stream->hikingTrail->nama }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-2 text-xs text-base-content/60">
                        @if ($stream->started_at)
                            Dimulai: {{ $stream->started_at->diffForHumans() }}
                        @else
                            Dibuat: {{ $stream->created_at->diffForHumans() }}
                        @endif
                    </div>

                    <div class="card-actions mt-4 justify-end">
                        @if ($stream->status === 'live')
                            <a class="btn btn-sm btn-primary" href="{{ route('live-cam.show', $stream->slug) }}"
                                target="_blank">
                                <x-gmdi-visibility-r class="size-4" />
                                Lihat Stream
                            </a>
                        @else
                            <a class="btn btn-sm btn-neutral"
                                href="{{ route('admin.live-stream.broadcast', $stream->slug) }}">
                                <x-gmdi-videocam-r class="size-4" />
                                Mulai Siaran
                            </a>
                        @endif
                        <form method="POST" action="{{ route('admin.live-stream.destroy', $stream->slug) }}"
                            onsubmit="return confirm('Yakin ingin menghapus stream ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-error btn-outline" type="submit">
                                <x-gmdi-delete-r class="size-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="alert">
                    <x-gmdi-info-r class="size-5" />
                    <span>Belum ada stream. Klik "Mulai Siaran" untuk membuat stream baru.</span>
                </div>
            </div>
        @endforelse
    </div>

    <x-slot:js></x-slot:js>
</x-layout.admin>
