<x-layout.app>

  <x-slot:title>{{ "Jalur Pendakian Gunung {$rute->gunung->nama} via $rute->nama" }}</x-slot:title>

  <div class="container mx-auto px-4 pb-12 pt-20 md:px-6 xl:px-8">
    <div class="no-scrollbar breadcrumbs text-sm">
      <ul>
        <li><a href="/">Home</a></li>
        <li class="font-bold">Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}</li>
      </ul>
    </div>

    <div class="mt-6 grid grid-cols-12 gap-x-8 gap-y-6">
      <div class="col-span-12 lg:col-span-4">
        <div class="sticky top-20 overflow-hidden rounded-md border border-base-300 shadow" x-data="jalurPendakianGallery"
          x-cloak>
          <div class="splide h-60 lg:h-64 xl:h-72" x-ref="splide">
            <div class="splide__track">
              <ul class="splide__list">
                <template x-for="url in urls" :key="url">
                  <li class="splide__slide">
                    <img class="expandable-image h-60 w-full cursor-pointer object-cover object-center lg:h-64 xl:h-72"
                      :data-splide-lazy="url" :alt="url" />
                  </li>
                </template>
              </ul>
            </div>
          </div>
          <div class="hoverable-scrollbar flex items-stretch gap-2 overflow-x-auto border-t border-base-300 p-4">
            <template x-for="(url, i) in urls" :key="url">
              <div class="size-16 shrink-0 grow-0 cursor-pointer overflow-hidden rounded-md border-2 xl:size-20"
                x-on:click="splide.go(i)" :class="activeIndex == i ? 'border-secondary' : 'border-base-content-300'">
                <img class="size-full object-cover object-center" :src="url" :alt="url" />
              </div>
            </template>
          </div>
        </div>
      </div>

      <div class="col-span-12 overflow-x-hidden lg:col-span-8">
        <div class="grid grid-cols-12 gap-6">
          <div class="col-span-12 xl:col-span-8" x-data="kaloriData">
            <h1 class="font-merriweather text-3xl font-extrabold">
              Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}
              @if ($rute->is_verified)
                <span class="tooltip tooltip-bottom align-middle" data-tip="Verified">
                  <x-gmdi-verified-r class="size-6 text-primary" />
                </span>
              @endif
            </h1>
            <div class="badge badge-primary mt-4 flex items-center gap-1 py-3">
              <x-gmdi-location-pin class="size-4" />
              @php
                // Determine location display based on available data
                $locationText = '';

                if ($rute->negara_id && $rute->negara && $rute->lokasi) {
                    // Use negara + lokasi approach for international routes
                    $negaraNama = $rute->negara->nama_lain ?? $rute->negara->nama;
                    $locationText = "{$rute->lokasi}, {$negaraNama}";
                } elseif ($rute->kode_desa && $rute->desa) {
                    // Use traditional Indonesian administrative hierarchy
                    $kecamatan = $rute->desa->kecamatan->nama_lain ?? $rute->desa->kecamatan->nama;
                    $kabupatenKota =
                        $rute->desa->kecamatan->kabupatenKota->nama_lain ?? $rute->desa->kecamatan->kabupatenKota->nama;
                    $provinsi =
                        $rute->desa->kecamatan->kabupatenKota->provinsi->nama_lain ??
                        $rute->desa->kecamatan->kabupatenKota->provinsi->nama;
                    $locationText = "$kecamatan, $kabupatenKota, $provinsi";
                } elseif ($rute->negara_id && $rute->negara) {
                    // Fallback to just negara if lokasi is not available
                    $locationText = $rute->negara->nama_lain ?? $rute->negara->nama;
                }
              @endphp
              <span>
                {{ $locationText }}
              </span>
            </div>
            <h2 class="mt-4">{{ $rute->deskripsi }}</h2>

            <div class="mt-6 flex flex-wrap justify-center gap-x-8 gap-y-4 lg:justify-start">
              <div>
                <div class="flex items-center gap-1">
                  <x-gmdi-keyboard-double-arrow-up-r class="size-4 text-error" />
                  <p class="font-medium text-base-content/80">Penambahan Elevasi</p>
                </div>
                <p class="text-center font-merriweather font-medium md:text-start">
                  {{ $penambahanElevasi ?? '-' }} m
                </p>
              </div>
              <div>
                <div class="flex items-center gap-1">
                  <x-gmdi-route-r class="size-4 text-neutral" />
                  <p class="font-medium text-base-content/80">Jarak Total</p>
                </div>
                <div class="flex items-center gap-1">
                  <p class="text-center font-merriweather font-medium md:text-start">
                    {{ $jarakTotal ?? '-' }} km
                  </p>
                  <div class="dropdown dropdown-end lg:dropdown-right lg:dropdown-top">
                    <div class="btn btn-circle btn-ghost btn-xs" tabindex="0" role="button">
                      <x-gmdi-info-outline-r class="size-4" />
                    </div>
                    <div
                      class="card dropdown-content card-compact z-[100] w-64 border border-base-300 bg-base-100 shadow"
                      tabindex="0">
                      <div class="card-body">
                        <p class="text-xs">
                          Nilai yang tertera adalah jarak total NAIK
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <div class="flex items-center gap-1">
                  <x-gmdi-alarm class="size-4 text-accent" />
                  <p class="font-medium text-base-content/80">Waktu Tempuh</p>
                </div>
                <div class="flex items-center gap-1">
                  <p class="text-center font-merriweather font-medium md:text-start">
                    {{ $waktuTempuhKumulatif ?? '-' }} jam
                  </p>
                  <div class="dropdown lg:dropdown-right lg:dropdown-top">
                    <div class="btn btn-circle btn-ghost btn-xs" tabindex="0" role="button">
                      <x-gmdi-info-outline-r class="size-4" />
                    </div>
                    <div class="card dropdown-content card-compact z-[100] w-48 bg-base-100 shadow" tabindex="0">
                      <div class="card-body">
                        <p class="text-xs">
                          Waktu tempuh ini adalah waktu yang digunakan untuk menempuh perjalanan NAIK saja dan tidak
                          memperhitungkan waktu yang digunakan untuk istirahat sepanjang perjalanan atau bermalam </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <div class="flex items-center gap-1">
                  <x-gmdi-local-fire-department-r class="size-4 text-success" />
                  <p class="font-medium text-base-content/80">Kalori</p>
                </div>
                <div class="flex items-center gap-1">
                  <p class = "text-center font-merriweather font-medium md:text-start">
                    <span x-text="kalori"></span> kkal
                  </p>
                  <div class="dropdown dropdown-end lg:dropdown-right lg:dropdown-top">
                    <div class="btn btn-circle btn-ghost btn-xs" tabindex="0" role="button">
                      <x-gmdi-info-outline-r class="size-4" />
                    </div>
                    <div
                      class="card dropdown-content card-compact z-[100] w-64 border border-base-300 bg-base-100 shadow"
                      tabindex="0">
                      <div class="card-body">
                        <p class="text-xs">
                          Nilai yang tertera adalah kalori yang digunakan untuk menempuh perjalanan NAIK dan TURUN
                          dengan
                          mengesampingkan kalori yang dibutuhkan saat istirahat sepanjang pendakian atau untuk bermalam
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            @if ($rute->rute_tingkat_kesulitan_id)
              <div class="mt-6">
                <div class="alert shadow" role="alert">
                  <x-gmdi-hiking-r class="size-6" />
                  <div>
                    <p class="font-medium">Jalur Pendakian Cocok Untuk {{ $rute->ruteTingkatKesulitan->nama }}</p>
                    @if ($rute->ruteTingkatKesulitan->deskripsi)
                      <p class="mt-2 text-sm text-base-content/70">{{ $rute->ruteTingkatKesulitan->deskripsi }}</p>
                    @endif
                  </div>
                </div>
              </div>
            @endif

            <div class="mt-4 flex flex-wrap items-center justify-center gap-4">
              @if ($kalori)
                <a class="btn btn-warning" href="{{ route('jalur-pendakian.slug.segmentasi', $rute->slug) }}">
                  Segmentasi Rute
                </a>
                <a class="btn btn-success" href="https://bekal.muncak.id/?kalori={{ $kalori }}" target="_blank"
                  rel="nofollow">
                  <span>bekal.muncak.id</span>
                  <x-gmdi-arrow-outward-r class="size-4" />
                </a>
              @endif
            </div>
          </div>

          <div class="col-span-12 xl:col-span-4" x-data="rute"
            :class="isFullscreen && 'fixed inset-0 z-[100] flex items-center justify-center bg-base-content/50'"
            x-cloak>
            <div :class="isFullscreen ? 'h-[32rem] w-full max-w-xl px-4' : 'h-56 xl:h-64'"
              x-on:click.outside="isFullscreen = false">
              <div class="relative size-full overflow-hidden rounded-md">
                <div class="z-[0] size-full" id="map"></div>
                <x-map-style-buttons />
                <x-map-fullscreen-button />
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <div class="border-b border-base-300 font-merriweather">
            <span class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
              Kondisi Cuaca
            </span>
          </div>
          <div class="mt-4">
            <div class="flex items-center gap-x-2">
              <x-gmdi-cloud-circle class="size-8" />
              <div>
                <p class="text-lg font-medium">{{ $lokasiPrediksiCuaca->nama }}</p>
                <p class="text-base-content/60">{{ number_format($lokasiPrediksiCuaca->elev, 0) }} m</p>
              </div>
            </div>
          </div>

          <div class="mt-6">
            @if (count($prediksiCuaca))
              <div class="grid grid-cols-2 gap-2 md:grid-cols-3 md:gap-4" id="auto-animate-2">
                @foreach ($prediksiCuaca as $item)
                  <div class="overflow-hidden rounded-box border border-base-300">
                    <div
                      class="{{ implode(' ', [
                          $item->weather >= 0 && $item->weather < 2 ? 'bg-yellow-500 dark:bg-gray-600' : '',
                          $item->weather >= 2 && $item->weather < 61 ? 'bg-gray-500 dark:bg-gray-600' : '',
                          $item->weather >= 61 && $item->weather <= 97 ? 'bg-gray-900 dark:bg-gray-950' : '',
                      ]) }} border-b border-base-300 py-2 text-center text-white">
                      <p class="text-lg font-semibold">{{ $item->day }}</p>
                      <p class="font-medium text-base-100 dark:text-base-content">
                        {{ "$item->date, $item->time" }}
                      </p>
                    </div>
                    <div
                      class="relative flex items-center justify-end gap-x-2 border-b border-base-200 bg-base-100 px-4 py-3">
                      <img class="absolute left-0 z-[1] mx-auto size-20 lg:size-24 xl:size-32"
                        src="{{ $item->image }}" alt="prediksi-cuaca">
                      <div class="relative z-[2] mt-2 space-y-1 text-end">
                        <p>{{ $item->weather_description }}</p>
                        <p class="text-2xl font-bold md:text-3xl">{{ $item->temperature }}</p>
                        <div class="flex items-center justify-end gap-2.5 text-sm md:text-base">
                          <div class="flex items-center justify-end gap-x-1.5">
                            <x-gmdi-wind-power-r class="size-4" />
                            <p>{{ $item->wind_speed }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              <p class="mt-4 text-sm font-medium text-base-content/70">
                &#9432; Kondisi cuaca di atas merupakan data dari <a class="hover:underline"
                  href="https://open-meteo.com/" target="_blank" rel="noreferer">Open Meteo</a>
              </p>
              <div class="mt-4 text-center">
                <a class="btn" href="{{ route('jalur-pendakian.slug.prediksi-cuaca', $rute->slug) }}">
                  Kondisi Cuaca Selengkapnya
                </a>
              </div>
            @else
              <div class="alert" role="alert">
                <x-gmdi-info-r class="size-8 shrink-0 text-info" />
                <div>
                  <h3 class="font-bold">Kondisi Cuaca Tidak Tersedia!</h3>
                  <div class="text-sm">Kondisi cuaca pada jalur pendakian ini belum tersedia.</div>
                </div>
              </div>
            @endif
          </div>
        </div>

        @if ($rute->informasi || $rute->aturan_dan_larangan)
          <div class="mt-10" x-data="{ show: '{{ $rute->informasi ? 'informasi' : ($rute->aturan_dan_larangan ? 'aturan_dan_larangan' : '') }}' }">
            <div class="no-scrollbar flex items-stretch overflow-x-auto border-b border-base-300 font-merriweather">
              @if ($rute->informasi)
                <button
                  class="inline-block shrink-0 grow-0 px-4 pb-2 font-semibold transition-colors md:text-lg"
                  x-on:click="show = 'informasi'"
                  :class="show == 'informasi' ? 'border-b-2 border-base-content' : 'border-0'">
                  Informasi
                </button>
              @endif
              @if ($rute->aturan_dan_larangan)
                <button class="inline-block shrink-0 grow-0 px-4 pb-2 font-semibold transition-colors md:text-lg"
                  x-on:click="show = 'aturan_dan_larangan'"
                  :class="show == 'aturan_dan_larangan' ? 'border-b-2 border-base-content' : 'border-0'">
                  Aturan dan Larangan
                </button>
              @endif
            </div>
            <div class="prose dark:prose-invert">
              @if ($rute->informasi)
                <div x-show="show == 'informasi'" x-cloak>
                  {!! $rute->informasi !!}
                </div>
              @endif
              @if ($rute->aturan_dan_larangan)
                <div x-show="show == 'aturan_dan_larangan'" x-cloak>
                  {!! $rute->aturan_dan_larangan !!}
                </div>
              @endif
            </div>
            <p class="text-sm font-medium text-base-content/70">
              &#9432; Terakhir diperbarui pada {{ $rute->updatedAtId }}
            </p>
          </div>
        @endif

        <div class="mt-10">
          <div class="border-b border-base-300 font-merriweather">
            <span
              class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
              Waypoints
            </span>
          </div>
          <div class="ml-2 mt-4">
            @if (count($waypoints))
              <ol class="relative border-s border-gray-400" id="auto-animate-4">
                @foreach ($waypoints as $item)
                  <li class="{{ $loop->last ? 'mb-0' : 'mb-10' }} ml-6 flex">
                    <div>
                      <x-gmdi-arrow-circle-right-r class="absolute -start-3 mt-1 size-6 bg-base-100" />
                      <div>
                        @if (count($item->gallery))
                          <div class="no-scrollbar mb-2 flex items-center justify-start gap-4 overflow-y-auto">
                            @foreach ($item->gallery as $url)
                              <img
                                class="expandable-image size-20 shrink-0 grow-0 rounded-sm object-cover object-center"
                                src="{{ $url }}" alt="point-gallery">
                            @endforeach
                          </div>
                        @endif
                        @if ($item->nama)
                          <p class="text-lg font-semibold">{{ $item->nama }}</p>
                        @endif
                        @if ($item->deskripsi)
                          <p class="mt-1 text-sm text-base-content/70">{{ $item->deskripsi }}</p>
                        @endif
                        </p>
                        <div class="mt-2 flex flex-wrap gap-x-4 text-base-content/70">
                          <div class="flex items-center gap-x-1">
                            <x-gmdi-keyboard-double-arrow-up-r class="size-4" />
                            <p>{{ $item->elev }} m</p>
                          </div>
                          @if (!$loop->first)
                            <div class="flex flex-wrap gap-x-4">
                              <div class="flex items-center gap-x-1">
                                <x-gmdi-alarm class="size-4" />
                                <p>{{ number_format($item->waktu_tempuh_kumulatif / 60, 1) }} jam</p>
                              </div>
                              <div class="flex items-center gap-x-1">
                                <x-gmdi-local-fire-department-r class="size-4" />
                                <p>{{ number_format($item->energi_kumulatif, 0) }} kkal</p>
                              </div>
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </li>
                @endforeach
              </ol>
            @else
              <div class="alert" role="alert">
                <x-gmdi-info-r class="size-8 shrink-0 text-info" />
                <div>
                  <h3 class="font-bold">Waypoint Tidak Tersedia!</h3>
                  <div class="text-sm">Waypoint pada jalur pendakian ini belum tersedia.</div>
                </div>
              </div>
            @endif
          </div>
          <div class="mt-4 text-center">
            <a class="btn" href="{{ route('jalur-pendakian.slug.segmentasi', $rute->slug) }}">
              Segmentasi Rute
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-12 grid grid-cols-12 gap-x-8 gap-y-6" x-data="ulasan">
      <div class="col-span-12 lg:col-span-4">
        @if (count($commentGallery))
          <div
            class="card card-compact sticky top-20 overflow-hidden rounded-lg border border-base-300 bg-base-100 shadow">
            <div class="card-body">
              <div>
                <h3 class="card-title font-merriweather font-semibold md:!text-lg">Gallery</h3>
                <p>Klik gambar untuk memperbesar dan melihatnya lebih jelas.</p>
              </div>
              <div class="splide" x-data="commentGallery" x-ref="splide">
                <div class="splide__track">
                  <ul class="splide__list">
                    @if (count($commentGallery) < 6)
                      @foreach ($commentGallery as $item)
                        <li class="splide__slide">
                          <img
                            class="expandable-image h-32 w-full cursor-pointer rounded-md object-cover object-center"
                            data-splide-lazy="{{ $item }}" alt="{{ $item }}">
                        </li>
                      @endforeach
                    @else
                      @php
                        $commentGallery = $commentGallery->chunk(2);
                      @endphp
                      @foreach ($commentGallery as $chunk)
                        <li class="splide__slide space-y-4">
                          @foreach ($chunk as $item)
                            <img
                              class="expandable-image h-32 w-full cursor-pointer rounded-md object-cover object-center"
                              data-splide-lazy="{{ $item }}" alt="{{ $item }}">
                          @endforeach
                        </li>
                      @endforeach
                    @endif
                  </ul>
                </div>
              </div>
              <div class="hidden text-center">
                <a class="btn btn-neutral btn-sm btn-block" href="/">
                  Lihat Gallery
                </a>
              </div>
            </div>
          </div>
        @endif
      </div>

      <div class="col-span-12 lg:col-span-8">
        <div class="border-b border-base-300 font-merriweather">
          <span class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
            Ulasan Pendaki
          </span>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-4">
          <p class="font-merriweather text-3xl font-semibold">{{ number_format($rute->comment_rating, 1) }}</p>
          <div class="mt-1 flex items-center gap-x-1">
            @php
              $rating = $rute->comment_rating;
              $fullStars = floor($rating);
              $halfStars = $rating - $fullStars >= 0.5 ? 1 : 0;
              $emptyStars = 5 - $fullStars - $halfStars;
            @endphp
            <div class="flex">
              @for ($i = 0; $i < $fullStars; $i++)
                <x-gmdi-star class="size-6 shrink-0 grow-0 text-yellow-500" />
              @endfor
              @if ($halfStars)
                <x-gmdi-star-half class="size-6 shrink-0 grow-0 text-yellow-500" />
              @endif
              @for ($i = 0; $i < $emptyStars; $i++)
                <x-gmdi-star-border-o class="size-6 shrink-0 grow-0 text-gray-500" />
              @endfor
            </div>
          </div>
          <p class="mt-2">{{ $rute->comment_count }} Ulasan</p>
        </div>
        <div class="mt-4 flex flex-col justify-between gap-4 md:flex-row">
          <button class="btn btn-primary btn-sm" type="button" onclick="ulasanForm.showModal()" x-data
            x-init="if (@json($errors->any())) { ulasanForm.showModal() }">
            Tulis Ulasan Anda
          </button>
          <div class="flex basis-1/3 items-center gap-2">
            <p class="shrink-0 grow-0 text-sm font-medium">Urutkan berdasarkan</p>
            <div class="shrink grow">
              <select class="select select-bordered select-sm w-full" id="order" name="order"
                x-on:change="handleOrdering">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="highestRating">Rating Tertinggi</option>
                <option value="lowestRating">Rating Terendah</option>
              </select>
            </div>
          </div>
        </div>

        <div id="auto-animate-5">
          <template x-if="!count && !error && !loading">
            <div class="alert mt-8" role="alert">
              <x-gmdi-chat-r class="size-6" />
              <div>
                <p class="font-semibold">Belum Ada Ulasan</p>
                <p>Jadilah yang pertama untuk berbagi pengalaman dan memberikan ulasan mengenai jalur pendakian ini!</p>
              </div>
            </div>
          </template>

          <template x-if="error">
            <div class="alert mt-8" role="alert">
              <x-gmdi-warning-r class="size-8 shrink-0 text-error" />
              <div>
                <h3 class="font-semibold">Error!</h3>
                <div class="text-sm">Terjadi error saat mendapatkan ulasan jalur pendakian ini.</div>
              </div>
              <button class="btn btn-sm" x-on:click="getData">Refresh</button>
            </div>
          </template>

          <template x-if="count">
            <div>
              <div class="mt-8 space-y-6" id="auto-animate-6">
                <template x-for="item in data">
                  <div class="flex items-stretch gap-x-4">
                    <img
                      class="sticky top-20 size-10 shrink-0 grow-0 rounded-md object-cover object-center md:size-12"
                      :src="item.user.avatar_url" alt="user-photo-profile" />
                    <div class="shrink grow">
                      <div class="flex justify-between">
                        <div class="shrink grow">
                          <p class="line-clamp-1 font-medium" x-text="item.user.name ?? item.user.username"></p>
                          <p class="line-clamp-1 text-sm text-base-content/70" x-text="`@${item.user.username}`"></p>
                        </div>
                        <p class="line-clamp-1 shrink-0 grow-0 text-sm text-base-content/70"
                          x-text="item.created_at_id"></p>
                      </div>
                      <div class="mt-2 rounded-md bg-base-200 px-4 py-2 md:py-4">
                        <p x-text="item.content"></p>
                        <div class="mt-2 flex items-center">
                          <template x-for="index in 5" :key="index">
                            <span
                              :class="{ 'text-yellow-500': index <= item.rating, 'text-gray-500': index > item.rating }">
                              <x-gmdi-star class="size-4 shrink-0 grow-0 md:size-5" />
                            </span>
                          </template>
                        </div>
                      </div>
                      <template x-if="item.gallery_urls.length">
                        <div class="no-scrollbar mt-2 flex shrink-0 grow-0 gap-2 overflow-x-auto">
                          <template x-for="url in item.gallery_urls">
                            <img
                              class="expandable-image size-14 shrink-0 grow-0 cursor-pointer rounded-md object-cover object-center md:size-16"
                              :src="url" alt="comment-gallery">
                          </template>
                        </div>
                      </template>
                    </div>
                  </div>
                </template>
              </div>
              <div class="mt-4 flex justify-end">
                <div class="join shrink-0 grow-0">
                  <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page--" :disabled="page == 1">
                    «
                  </button>
                  <template x-for="item in pagination">
                    <button class="btn join-item btn-xs sm:btn-sm" :disabled="!item"
                      :class="page == item && 'btn-active'" x-on:click="page = item" x-text="item || '..'">
                    </button>
                  </template>
                  <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page++"
                    :disabled="page == Math.ceil(count / take)">
                    »
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <dialog class="modal" id="imageModal">
      <div class="modal-box max-w-screen-sm !p-0" x-data x-on:click.outside="imageModal.close()">
        <form class="absolute right-2 top-2 flex justify-end" method="dialog">
          <button class="btn btn-square btn-sm">
            <x-gmdi-close-r />
          </button>
        </form>
        <img class="h-60 w-full object-cover object-center sm:h-72 md:h-80 lg:h-96" src="" alt="gallery">
      </div>
    </dialog>

    <dialog class="modal" id="ulasanForm">
      <div class="modal-box max-w-screen-sm">
        <form class="absolute right-4 top-4 flex justify-end" method="dialog">
          <button class="btn btn-square btn-ghost btn-sm">
            <x-gmdi-close-r />
          </button>
        </form>
        <h3 class="text-lg font-bold">Ulasan Jalur Pendakian</h3>
        <p class="text-base-content/70">Tulis ulasan Anda dengan mengisi formulir di bawah ini!</p>
        <form class="space-y-4" action="{{ route('ulasan.store', $rute->slug) }}" enctype="multipart/form-data"
          method="post">
          @csrf
          <div x-data="{ rating: @json((int) old('rating', 0)) }">
            <div class="label">
              <span class="required label-text font-medium">
                Rating
              </span>
            </div>
            <input id="rating" type="hidden" name="rating" x-model="rating">
            <div class="flex items-center gap-x-1">
              <button class="btn btn-square btn-ghost btn-sm" type="button" x-on:click="rating = 1"
                :class="rating > 0 ? 'text-yellow-500' : 'text-gray-500'">
                <x-gmdi-star-r class="size-7" />
              </button>
              <button class="btn btn-square btn-ghost btn-sm" type="button" x-on:click="rating = 2"
                :class="rating > 1 ? 'text-yellow-500' : 'text-gray-500'">
                <x-gmdi-star-r class="size-7" />
              </button>
              <button class="btn btn-square btn-ghost btn-sm" type="button" x-on:click="rating = 3"
                :class="rating > 2 ? 'text-yellow-500' : 'text-gray-500'">
                <x-gmdi-star-r class="size-7" />
              </button>
              <button class="btn btn-square btn-ghost btn-sm" type="button" x-on:click="rating = 4"
                :class="rating > 3 ? 'text-yellow-500' : 'text-gray-500'">
                <x-gmdi-star-r class="size-7" />
              </button>
              <button class="btn btn-square btn-ghost btn-sm" type="button" x-on:click="rating = 5"
                :class="rating > 4 ? 'text-yellow-500' : 'text-gray-500'">
                <x-gmdi-star-r class="size-7" />
              </button>
            </div>
            @error('rating')
              <div class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </div>
            @enderror
          </div>

          <x-form.textarea name="content"
            placeholder="Berikan ulasan dan pengalaman Anda mengenai jalur pendakian ini" label="Ulasan"
            :value="old('content')" required />

          <x-form.image-multiple name="gallery" label="Gallery: Maksimal 3 Gambar (jpg, jpeg, png, webp)" />

          <div class="modal-action flex-row-reverse justify-start gap-2">
            <button class="btn btn-primary btn-sm" type="submit">Simpan Ulasan</button>
            <button class="btn btn-sm" type="button" x-on:click="ulasanForm.close()">Batal</button>
          </div>
        </form>
      </div>
    </dialog>

    <div class="fixed bottom-6 right-6 z-[2] md:bottom-8 md:right-8">
      <button class="btn btn-circle btn-secondary btn-sm shadow-md md:btn-md"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
        <x-gmdi-keyboard-arrow-up-o class="size-6 md:size-8" />
      </button>
    </div>
  </div>

  <x-slot:js>
    <script title="alpine.js">
      let map;

      const ruteSlug = '{{ $rute->slug }}'
      const gunungSlug = '{{ $rute->gunung->slug }}'

      function rute() {
        return {
          rute: null,
          mapStyle: this.$persist('outdoor').as('map-style'),
          lngLatGunung: [{{ $rute->gunung->long }}, {{ $rute->gunung->lat }}],
          isFullscreen: false,

          init() {
            this.getRute();
            this.$watch('mapStyle', (val) => {
              if (val) {
                map.setStyle(`https://api.maptiler.com/maps/${val}/style.json?key={{ env('MAP_STYLE_KEY') }}`)
                map.once('styledata', () => {
                  this.initMap();
                });
              };
            });
            this.$watch('isFullscreen', (val) => {
              $('body').toggleClass('overflow-y-hidden')
            });
          },

          getRute() {
            $.ajax({
              url: `{{ route('api.rute.rute', $rute->id) }}`,
              method: 'GET',
              dataType: 'json',
              success: (data) => {
                this.rute = JSON.parse(data);
                if (this.rute) {
                  this.initMap()
                }
              },
              error: (error) => {
                this.error = true;
              }
            });
          },

          initMap() {
            if (!map) {
              map = new maplibregl.Map({
                container: 'map',
                style: `https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`,
                center: this.lngLatGunung,
                zoom: 14,
              });

              map.on('load', () => {
                this.addLine()
              })
            } else {
              this.addLine()
            }
          },

          addLine() {
            map.addSource('route', {
              type: 'geojson',
              data: this.rute,
            });

            map.addLayer({
              id: 'route',
              type: 'line',
              source: 'route',
              layout: {
                'line-join': 'round',
                'line-cap': 'round',
              },
              paint: {
                'line-color': '#0069ff',
                'line-width': 5,
              },
            });

            const bounds = new maplibregl.LngLatBounds();
            this.rute.coordinates.forEach(coord => {
              bounds.extend(coord);
            });

            map.fitBounds(bounds, {
              padding: 50,
              maxZoom: 24,
              duration: 2000,
            });
          }
        }
      }

      function jalurPendakianGallery() {
        return {
          urls: @json($rute->getGalleryUrls()),
          splide: null,
          activeIndex: 0,
          init() {
            this.$nextTick(() => {
              this.splide = new Splide(this.$refs.splide, {
                perPage: 1,
                autoplay: true,
                arrows: false,
                pagination: true,
                rewind: true,
                lazyLoad: "nearby",
              }).mount()

              this.splide.on('move', (newIndex) => {
                this.activeIndex = newIndex;
              });
            })
          }
        }
      }

      @if (count($commentGallery))
        function commentGallery() {
          return {
            splide: null,
            init() {
              this.splide = new Splide(this.$refs.splide, {
                perPage: 2,
                fixedWidth: "10rem",
                gap: "1rem",
                pagination: false,
                lazyLoad: "nearby",
              }).mount();
            }
          }
        }
      @endif

      function ulasan() {
        return {
          data: [],
          count: 0,
          rating: 0,
          loading: false,
          error: false,
          take: 20,
          page: 1,
          order: 'creation',
          direction: 'desc',

          get pagination() {
            const maxPage = Math.ceil(this.count / this.take);
            let pages = [];

            if (maxPage <= 6)
              pages = Array.from({
                length: maxPage
              }, (_, i) => i + 1);
            else if (this.page <= 4)
              pages = [1, 2, 3, 4, 5, null, maxPage];
            else if (this.page >= 5 && this.page <= maxPage - 4)
              pages = [1, null, this.page - 1, this.page, this.page + 1, null, maxPage];
            else
              pages = [1, null, maxPage - 4, maxPage - 3, maxPage - 2, maxPage - 1, maxPage];

            return pages;
          },

          init() {
            this.getData();
            this.$watch('page', () => this.getData())
          },

          getData() {
            this.loading = true;
            this.error = false;
            $.ajax({
              url: `{{ route('api.rute.ulasan.index', $rute->id) }}?take=${this.take}&page=${this.page}&order=${this.order}&direction=${this.direction}`,
              method: 'GET',
              dataType: 'json',
              success: (data) => {
                this.data = data.data;
                this.count = data.count;
                this.rating = data.rating;
                this.error = false;
              },
              error: (error) => {
                this.error = true;
              },
              complete: () => {
                this.loading = false;
              }
            });
          },

          handleOrdering() {
            const val = $(this.$el).val();
            if (val == 'newest') {
              this.order = 'creation';
              this.direction = 'desc';
            }
            if (val == 'oldest') {
              this.order = 'creation';
              this.direction = 'asc';
            }
            if (val == 'highestRating') {
              this.order = 'rating';
              this.direction = 'desc';
            }
            if (val == 'lowestRating') {
              this.order = 'rating';
              this.direction = 'asc';
            }
            this.getData();
          }
        }
      }

      $(function() {
        $(document).on('click', '.expandable-image', function() {
          $('#imageModal img').attr('src', $(this).attr('src'));
          $('#imageModal img').attr('alt', $(this).attr('src'));
          imageModal.showModal();
        })
      })

      function kaloriData() {
        return {
          kalori: "{{ $kalori }}",
          init() {
            $.get({
              url: `/api/rute/{{ $rute->id }}/segmentasi`,
              success: (res) => {
                let totalEnergi = 0;

                res.forEach(segment => {
                  if (segment.prediksi_cuaca && segment.prediksi_cuaca.length > 0) {
                    totalEnergi += segment.prediksi_cuaca[0].energi || 0;
                  }
                });

                this.kalori = totalEnergi.toFixed(0);
              },
            });
          }
        }
      }
    </script>

    <script type="module">
      import autoAnimate from "{{ asset('js/auto-animate.min.js') }}"
      $(function() {
        autoAnimate(document.getElementById('auto-animate-4'));
        autoAnimate(document.getElementById('auto-animate-5'));
      })
    </script>
  </x-slot:js>

  <x-slot:head>
    <meta name="description" content="{{ $rute->deskripsi }}">
    <meta name="keywords"
      content="jalur pendakian, jalur pendakian gunung, rute pendakian, pendakian gunung, informasi jalur pendakian, jalur gunung">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url("/jalur-pendakian/{$rute->slug}") }}" hreflang="id" />
    <link rel="canonical" href="{{ url("/jalur-pendakian/{$rute->slug}") }}" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="Jalur Pendakian Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}">
    <meta property="og:description" content="{{ $rute->deskripsi }}">
    <meta property="og:url" content="{{ url("/jalur-pendakian/{$rute->slug}") }}">
    <meta property="og:site_name" content="muncak.id">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>

</x-layout.app>
