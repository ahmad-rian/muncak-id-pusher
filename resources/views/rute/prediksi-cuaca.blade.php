<x-layout.app>

  <x-slot:title>{{ "Prediksi Cuaca Gunung {$rute->gunung->nama} via $rute->nama" }}</x-slot:title>

  <div class="container mx-auto px-4 pb-12 pt-20 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
    <div class="no-scrollbar breadcrumbs text-sm">
      <ul>
        <li><a href="/">Home</a></li>
        <li>
          <a href="{{ route('jalur-pendakian.slug', $rute->slug) }}">
            Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}
          </a>
        </li>
        <li class="font-bold">Prediksi Cuaca</li>
      </ul>
    </div>

    <div class="mt-6">
      <div class="mx-auto max-w-screen-lg text-center">
        <p class="text-base-content/80">Prediksi Cuaca</p>
        <div class="mt-2 flex items-center justify-center gap-2">
          <h1 class="shrink grow-0 font-merriweather text-2xl font-extrabold md:text-3xl">
            Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}
          </h1>
          @if ($rute->is_verified)
            <div class="tooltip shrink-0 grow-0" data-tip="Verified">
              <x-gmdi-verified-r class="size-6 text-primary" />
            </div>
          @endif
        </div>
        <div class="badge badge-primary mx-auto mt-3 flex items-center gap-1 py-3">
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
                $kabupatenKota = $rute->desa->kecamatan->kabupatenKota->nama_lain ?? $rute->desa->kecamatan->kabupatenKota->nama;
                $provinsi = $rute->desa->kecamatan->kabupatenKota->provinsi->nama_lain ?? $rute->desa->kecamatan->kabupatenKota->provinsi->nama;
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
        <h2 class="custom-scrollbar mt-4 max-h-[calc(1.5*5rem)] overflow-y-auto text-center text-base-content/70">
          {{ $rute->deskripsi }}
        </h2>
      </div>
      <div class="mt-6 border-y border-base-300 py-4">
        <div class="flex items-center justify-center gap-x-3">
          <x-gmdi-cloud-circle-r class="size-10" />
          <div>
            <p class="text-lg font-medium">{{ $lokasiPrediksiCuaca->nama }}</p>
            <p class="text-base-content/60">{{ number_format($lokasiPrediksiCuaca->elev, 0) }} m</p>
          </div>
        </div>
      </div>

      <div class="mt-6">
        <div class="space-y-10">
          @if ($prediksiCuaca->count())
            @foreach ($prediksiCuaca as $date => $items)
              <div>
                <p class="text-center font-merriweather text-xl font-semibold">
                  {{ $items[0]->day . ', ' . $items[0]->date }}
                </p>

                <div class="no-scrollbar mt-4 flex items-center justify-start overflow-y-auto">
                  @foreach ($items as $key => $item)
                    <div
                      class="@if ($key != 0) border-l border-base-300 @endif shrink-0 grow-0 px-4 py-1 text-center">
                      <p class="badge badge-outline text-base-content/70">{{ $item->time }}</p>
                      <img class="mx-auto mt-1 size-24" src="{{ $item->image }}" alt="prediksi-cuaca">
                      <div>
                        <p class="text-base font-semibold">{{ $item->weather_description }}</p>
                        <div class="mt-1 flex items-center justify-center gap-1.5 text-xs text-base-content/70">
                          <div class="flex shrink-0 grow-0 items-center justify-end gap-x-0.5">
                            <x-gmdi-wind-power-r class="size-3 shrink-0 grow-0" />
                            <p class="shrink-0 grow-0">{{ number_format($item->wind_speed, 1) }} km/j</p>
                          </div>
                          <div class="flex shrink-0 grow-0 items-center justify-end gap-x-0.5">
                            <x-gmdi-thermostat-r class="size-3 shrink-0 grow-0" />
                            <p class="shrink-0 grow-0">{{ number_format($item->temperature, 1) }} °C</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
            <p class="mt-6 text-center text-sm text-base-content/70">
              &#9432; Kondisi cuaca di atas merupakan data dari <a href="https://open-meteo.com/" class="hover:underline" target="_blank" rel="noreferer">Open Meteo</a>
            </p>
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
    </div>
  </div>

  <x-slot:head>
    <meta name="description" content="{{ $rute->deskripsi }}">
    <meta name="keywords"
      content="jalur pendakian, jalur pendakian gunung, rute pendakian, pendakian gunung, informasi jalur pendakian, jalur gunung">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url("/jalur-pendakian/{$rute->slug}/prediksi-cuaca") }}" hreflang="id" />
    <link rel="canonical" href="{{ url("/jalur-pendakian/{$rute->slug}/prediksi-cuaca") }}" />
    <meta property="og:type" content="website">
    <meta property="og:title"
      content="Jalur Pendakian Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }} - Prediksi Cuaca">
    <meta property="og:description" content="{{ $rute->deskripsi }}">
    <meta property="og:url" content="{{ url("/jalur-pendakian/{$rute->slug}/prediksi-cuaca") }}">
    <meta property="og:site_name" content="muncak.id">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>

</x-layout.app>
