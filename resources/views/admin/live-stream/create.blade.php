<x-layout.admin>
    <div class="breadcrumbs text-sm text-base-content/70">
        <ul>
            <li><a href="{{ route('admin.live-stream.index') }}">Live Streaming</a></li>
            <li>Buat Stream Baru</li>
        </ul>
    </div>

    <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
        <div>
            <p class="text-2xl font-semibold">Buat Live Stream Baru</p>
            <p class="text-sm text-base-content/70 mt-1">Isi informasi stream sebelum memulai siaran</p>
        </div>
    </div>

    <div class="mt-6">
        <div class="rounded-lg border border-base-300 bg-base-100">
            <div class="p-6">
                <form method="POST" action="{{ route('admin.live-stream.store') }}">
                    @csrf

                    <!-- Jalur Pendakian Selection with Search -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Pilih Jalur Pendakian <span class="text-error">*</span></span>
                        </label>
                        <select id="hiking-trail-select" name="hiking_trail_id" class="select select-bordered w-full" required>
                            <option value="" disabled selected>Cari dan pilih jalur pendakian...</option>
                            @foreach ($hikingTrails as $trail)
                                <option value="{{ $trail->id }}"
                                    {{ old('hiking_trail_id') == $trail->id ? 'selected' : '' }}>
                                    {{ $trail->nama }}
                                    @if ($trail->gunung)
                                        ({{ $trail->gunung->nama }})
                                    @endif
                                    @if ($trail->gunung && $trail->gunung->kabupatenKota && $trail->gunung->kabupatenKota->provinsi)
                                        - {{ $trail->gunung->kabupatenKota->provinsi->nama }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('hiking_trail_id')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Judul Stream <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" class="input input-bordered w-full"
                            placeholder="Contoh: Pendakian Gunung Rinjani - Summit Attack" value="{{ old('title') }}"
                            required maxlength="255">
                        @error('title')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                        <label class="label">
                            <span class="label-text-alt">Maks. 255 karakter</span>
                        </label>
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Deskripsi</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered h-24 w-full"
                            placeholder="Ceritakan tentang rencana pendakian Anda..." maxlength="1000">{{ old('description') }}</textarea>
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                        <label class="label">
                            <span class="label-text-alt">Maks. 1000 karakter (opsional)</span>
                        </label>
                    </div>

                    <!-- Quality Selection -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Kualitas Stream Default</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="quality" value="360p" class="radio radio-primary"
                                    {{ old('quality', '720p') == '360p' ? 'checked' : '' }} />
                                <span class="label-text">360p (Hemat Data)</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="quality" value="720p" class="radio radio-primary"
                                    {{ old('quality', '720p') == '720p' ? 'checked' : '' }} />
                                <span class="label-text">720p (Recommended)</span>
                            </label>
                            <label class="label cursor-pointer gap-2">
                                <input type="radio" name="quality" value="1080p" class="radio radio-primary"
                                    {{ old('quality', '720p') == '1080p' ? 'checked' : '' }} />
                                <span class="label-text">1080p (HD)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info mb-6">
                        <x-gmdi-info-r class="h-5 w-5" />
                        <div>
                            <h4 class="font-bold">Tips untuk Live Streaming:</h4>
                            <ul class="text-sm mt-2 list-disc list-inside">
                                <li>Pastikan sinyal internet stabil (min. 5 Mbps upload)</li>
                                <li>Charge baterai penuh atau gunakan power bank</li>
                                <li>Test kamera dan mikrofon sebelum mulai</li>
                                <li>Interaksi dengan viewers melalui chat</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 justify-end">
                        <a href="{{ route('admin.live-stream.index') }}" class="btn btn-ghost">
                            <x-gmdi-arrow-back-r class="h-5 w-5" />
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-gmdi-videocam-r class="h-5 w-5" />
                            Lanjut ke Broadcaster
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-slot:js>
        <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
        <link rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const element = document.getElementById('hiking-trail-select');
                const choices = new Choices(element, {
                    searchEnabled: true,
                    searchPlaceholderValue: 'Ketik untuk mencari jalur pendakian...',
                    noResultsText: 'Jalur pendakian tidak ditemukan',
                    itemSelectText: 'Klik untuk pilih',
                    shouldSort: false,
                    searchResultLimit: 10,
                });
            });
        </script>
    </x-slot:js>
</x-layout.admin>
