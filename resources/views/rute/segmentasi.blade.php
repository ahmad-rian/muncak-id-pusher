<x-layout.app>

  <x-slot:title>{{ "Segmentasi Jalur Gunung {$rute->gunung->nama} via $rute->nama" }}</x-slot:title>

  <div class="container mx-auto px-4 pb-12 pt-20 md:px-6 lg:px-8 xl:px-12 2xl:px-16" x-data="segmentasi">
    <div class="no-scrollbar breadcrumbs text-sm">
      <ul>
        <li><a href="/">Home</a></li>
        <li>
          <a href="{{ route('jalur-pendakian.slug', $rute->slug) }}">
            Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }}
          </a>
        </li>
        <li class="font-bold">Segmentasi</li>
      </ul>
    </div>

    <div class="mt-6">
      <p class="text-md text-center text-base-content/80">Segmentasi Jalur</p>
      <div class="mt-2 flex items-center justify-center gap-2 text-center">
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
    </div>

    <form class="mx-auto mt-6 grid max-w-screen-md grid-cols-1 items-end gap-2 md:grid-cols-12"
      x-on:submit.prevent="location.reload()">
      <label class="form-control w-full md:col-span-4 md:max-w-xs">
        <div class="label">
          <span class="label-text">Berat Badan Anda (kg)</span>
        </div>
        <label class="input input-sm input-bordered flex items-center gap-2">
          <x-gmdi-person-r class="size-4 opacity-70" />
          <input class="grow" placeholder="Berat Badan Anda" type="number" step="0.1" min="1"
            x-model.number.lazy="berat_orang" />
        </label>
      </label>
      <label class="form-control w-full md:col-span-4 md:max-w-xs">
        <div class="label">
          <span class="label-text">Berat Beban Naik (kg)</span>
        </div>
        <label class="input input-sm input-bordered flex items-center gap-2">
          <x-gmdi-shopping-bag-r class="size-4 opacity-70" />
          <input class="grow" placeholder="Berat Beban Naik" type="number" step="0.1" min="1"
            x-model.number.lazy="berat_beban_naik" />
        </label>
      </label>
      <label class="form-control w-full md:col-span-3 md:max-w-xs">
        <div class="label">
          <span class="label-text">Kecepatan Pendakian</span>
        </div>
        <label class="input input-sm input-bordered flex items-center gap-2">
          <x-gmdi-timer-r class="size-4 opacity-70" />
          <select class="grow bg-transparent outline-none" x-model.number="skala_waktu">
            <option value="2">Sangat Cepat</option>
            <option value="1.8">Lebih Cepat</option>
            <option value="1.6">Cepat</option>
            <option value="1.4">Sedikit Cepat</option>
            <option value="1.2">Agak Cepat</option>
            <option value="1">Normal</option>
            <option value="0.9">Sedikit Lambat</option>
            <option value="0.8">Lambat</option>
            <option value="0.7">Lebih Lambat</option>
            <option value="0.6">Sangat Lambat</option>
            <option value="0.5">Ekstrim Lambat</option>
            <option value="0.4">Sangat Ekstrim Lambat</option>
            <option value="0.3">Ultra Lambat</option>
            <option value="0.2">Super Ultra Lambat</option>
            <option value="0.1">Hampir Tidak Bergerak</option>
            <option value="0">Sangat Lambat</option>
          </select>
        </label>
      </label>
      <button class="btn btn-primary btn-sm md:col-span-1">Ubah</button>
    </form>

    <div class="mt-6" id="auto-animate-1">
      <template x-if="!loading && !error && result.length">
        <div class="grid grid-cols-12 gap-6">
          <div class="col-span-3 hidden lg:block">
            <div class="card card-compact rounded-box border border-base-300 lg:sticky lg:top-20">
              <div class="card-body" x-data="{ open: $persist(true).as('menu-segmentasi') }">
                <button class="card-title flex items-center gap-2" type="button" x-on:click="open = !open">
                  <span class="btn btn-square btn-sm">
                    <x-gmdi-menu-r class="size-4 opacity-70" />
                  </span>
                  <p class="line-clamp-1 shrink grow text-start">Menu Segmentasi</p>
                </button>
                <div x-show="open" x-collapse>
                  <ul class="menu w-full">
                    <template x-for="(item, i) in result" :key="item.no">
                      <div>
                        <template x-if="i <= 0">
                          <li>
                            <button type="button" x-on:click="segmentasi_active = i"
                              :class="segmentasi_active == i && 'active menu-dropdown-show'"
                              x-text="'Segmentasi' + ' ' + item.no">
                            </button>
                          </li>
                        </template>
                        <template x-if="i > 0">
                          <div>
                            <template x-if="!userId">
                              <li>
                                <a href="{{ route('auth.sign-up') }}">
                                  <x-gmdi-lock-r class="size-4 opacity-70" />
                                  <span x-text="'Segmentasi' + ' ' + item.no"></span>
                                </a>
                              </li>
                            </template>
                            <template x-if="userId">
                              <li>
                                <button type="button" x-on:click="segmentasi_active = i"
                                  :class="segmentasi_active == i &&
                                      'active menu-dropdown-show'"
                                  x-text="'Segmentasi' + ' ' + item.no">
                                </button>
                              </li>
                            </template>
                          </div>
                        </template>
                      </div>
                    </template>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="drawer lg:hidden">
            <input class="drawer-toggle" id="my-drawer" type="checkbox" />
            <div class="drawer-content">
              <label class="btn btn-primary drawer-button btn-sm fixed right-4 top-20 z-[100]" for="my-drawer">
                <x-gmdi-ssid-chart class="size-4" />
                <span>Segmentasi</span>
              </label>
            </div>
            <div class="drawer-side z-[100]">
              <label class="drawer-overlay" for="my-drawer" aria-label="close sidebar"></label>
              <div class="min-h-full w-[50%] bg-base-100 p-4 sm:max-w-80">
                <div class="card-title flex items-center gap-2">
                  <span class="btn btn-square btn-sm">
                    <x-gmdi-menu-r class="size-4 opacity-70" />
                  </span>
                  <p class="line-clamp-1 shrink grow text-start text-base">Segmentasi</p>
                </div>
                <ul class="menu mt-4 w-full p-0">
                  <template x-for="(item, i) in result" :key="item.no">
                    <div>
                      <template x-if="i <= 0">
                        <li>
                          <button type="button" x-on:click="segmentasi_active = i"
                            :class="segmentasi_active == i && 'active menu-dropdown-show'"
                            x-text="'Segmen' + ' ' + item.no">
                          </button>
                        </li>
                      </template>
                      <template x-if="i > 0">
                        <div>
                          <template x-if="!userId">
                            <li>
                              <a href="{{ route('auth.sign-up') }}">
                                <x-gmdi-lock-r class="size-4 opacity-70" />
                                <span x-text="'Segmen' + ' ' + item.no"></span>
                              </a>
                            </li>
                          </template>
                          <template x-if="userId">
                            <li>
                              <button type="button" x-on:click="segmentasi_active = i"
                                :class="segmentasi_active == i &&
                                    'active menu-dropdown-show'"
                                x-text="'Segmen' + ' ' + item.no">
                              </button>
                            </li>
                          </template>
                        </div>
                      </template>
                    </div>
                  </template>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-span-12 space-y-8 lg:col-span-9">
            <div>
              <div class="border-b border-base-300 font-merriweather">
                <span
                  class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
                  Jarak Tempuh
                </span>
              </div>
              <div class="mt-4 flex items-center justify-start gap-4">
                <x-gmdi-directions-walk class="size-8 shrink-0 grow-0" />
                <div>
                  <p class="font-medium text-base-content/70" x-text="`Jarak Tempuh Segmen ${segmentasi_active + 1}`">
                  </p>
                  <p class="text-lg">
                    <span x-text="(segmentasi_data?.jarak_total).toFixed(0)"></span>
                    <span>m</span>
                  </p>
                </div>
              </div>
            </div>

            <div>
              <div class="border-b border-base-300 font-merriweather">
                <span
                  class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
                  Rute dan Grafik
                </span>
              </div>
              <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="relative h-80 w-full">
                  <div class="size-full overflow-hidden rounded-box">
                    <div class="size-full" id="map" x-init="calculateMap"></div>
                  </div>
                  <x-map-style-buttons />
                </div>
                <div class="card card-compact h-80 w-full border border-base-300">
                  <div class="card-body !px-0 !py-2">
                    <canvas class="size-full dark:invert" x-ref="chartCanvas" x-init="calculateChart"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <div class="flex border-b border-base-300 font-merriweather">
                <div class="border-b border-base-300 font-merriweather">
                  <span
                    class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold md:text-lg">
                    Prediksi Cuaca Dini Hari
                  </span>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-1 gap-2 md:gap-4">
                <template x-for="(item, i) in segmentasi_data?.prediksi_cuaca" :key="item.datetime">
                  <div
                    class="card border border-base-200 bg-gradient-to-br from-base-100 to-base-200/50 shadow-lg transition-all duration-300 hover:shadow-xl">
                    <div class="card-body">
                      <!-- Header Section -->
                      <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                          <div class="avatar placeholder">
                            <div
                              class="h-12 w-12 rounded-full bg-primary text-primary-content">
                              <span class="text-lg font-bold" x-text="item.day.substring(0, 3)"></span>
                            </div>
                          </div>
                          <div>
                            <h3 class="text-lg font-bold" x-text="item.day"></h3>
                            <p class="text-sm text-base-content/70" x-text="item.date + ' • ' + item.time"></p>
                          </div>
                        </div>
                        <div class="text-right">
                          <div class="stat-value text-3xl font-bold text-primary"
                            x-text="typeof item.t === 'number' ? item.t.toFixed(0) + '°C' : item.t">
                          </div>
                          <div
                            class="flex items-center justify-end gap-2 text-sm text-base-content/70">
                            <x-gmdi-air-r class="size-4" />
                            <span x-text="typeof item.ws === 'number' ? item.ws.toFixed(0) + ' km/j' : item.ws"></span>
                          </div>
                        </div>
                      </div>

                      <!-- Weather Condition Section -->
                      <div class="mb-4 rounded-lg bg-base-200/30 p-4">
                        <div class="flex items-center gap-4">
                          <div class="flex-shrink-0">
                            <img class="size-16 drop-shadow-lg" :src="item.image" alt="Weather Condition">
                          </div>
                          <div class="flex-1">
                            <h4 class="mb-1 text-lg font-semibold" x-text="item.weather_desc"></h4>
                            <div class="flex flex-wrap gap-4 text-sm">
                              <div class="flex items-center gap-1">
                                <x-gmdi-thermostat-r class="size-4 text-error" />
                                <span>Suhu: <strong
                                    x-text="typeof item.t === 'number' ? item.t.toFixed(0) + '°C' : item.t"></strong></span>
                              </div>
                              <div class="flex items-center gap-1">
                                <x-gmdi-air-r class="size-4 text-info" />
                                <span>Angin: <strong
                                    x-text="typeof item.ws === 'number' ? item.ws.toFixed(0) + ' km/j' : item.ws"></strong></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Key Metrics Grid -->
                      <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <!-- Waktu Tempuh -->
                        <div class="rounded-lg border border-primary/20 bg-primary/10 p-3">
                          <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary p-2">
                              <x-gmdi-schedule-r class="size-5 text-primary-content" />
                            </div>
                            <div>
                              <p
                                class="text-xs font-medium uppercase tracking-wide text-primary">
                                Waktu Tempuh</p>
                              <p class="text-xl font-bold text-primary"
                                x-text="item.waktu_tempuh_m.toFixed(0) + ' menit'"></p>
                            </div>
                          </div>
                        </div>

                        <!-- Energi -->
                        <div class="rounded-lg border border-success/20 bg-success/10 p-3">
                          <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-warning p-2">
                              <x-gmdi-bolt-r class="size-5 text-warning-content" />
                            </div>
                            <div>
                              <p
                                class="text-xs font-medium uppercase tracking-wide">
                                Kalori Terbakar</p>
                              <p class="text-xl font-bold" x-text="item.energi.toFixed(0) + ' kkal'"></p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Additional Info Grid -->
                      <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                        <!-- Kebutuhan Air -->
                        <div class="rounded-lg border border-primary/10 bg-primary/5 p-3">
                          <div class="mb-2 flex items-center gap-2">
                            <x-gmdi-water-drop-r class="size-4 text-primary" />
                            <span
                              class="text-xs font-semibold uppercase text-primary">
                              Kebutuhan Air Saat Naik
                            </span>
                          </div>
                          <p class="text-lg font-bold" x-text="(item.air_minum_prediksi * 1000).toFixed(0) + ' ml'">
                          </p>
                          <p class="text-xs text-base-content/60">Untuk segmen ini</p>
                        </div>

                        <!-- Kondisi Naik -->
                        <div class="rounded-lg border border-success/10 bg-success/5 p-3">
                          <div class="mb-2 flex items-center gap-2">
                            <x-gmdi-trending-up-r class="size-4 text-success" />
                            <span
                              class="text-xs font-semibold uppercase text-success">Kondisi
                              Naik</span>
                          </div>

                          <p class="text-sm font-semibold">
                            <span class="text-base-content/80">Beban Lutut: </span>
                            <span x-text="item.keterangan_naik.keterangan"
                              :class="{
                                  'text-success': item.keterangan_naik.keterangan.toLowerCase().includes('ringan'),
                                  'text-warning': item.keterangan_naik.keterangan.toLowerCase().includes('sedang'),
                                  'text-error': item.keterangan_naik.keterangan.toLowerCase().includes('berat') || item
                                      .keterangan_naik.keterangan.toLowerCase().includes('sulit')
                              }"></span>
                          </p>
                          <p class="text-xs text-base-content/60">Tingkat beban pada persendian lutut</p>
                          <div class="mt-2 space-y-1">

                            <template
                              x-if="item.keterangan_naik.keterangan.toLowerCase().includes('ringan')">
                              <p class="text-xs text-success">Minimal - Lutut tidak
                                terlalu terbebani</p>
                            </template>
                            <template
                              x-if="item.keterangan_naik.keterangan.toLowerCase().includes('sedang')">
                              <p class="text-xs text-warning">Sedang - Gunakan trekking
                                pole untuk mengurangi beban lutut</p>
                            </template>
                            <template
                              x-if="item.keterangan_naik.keterangan.toLowerCase().includes('berat') || item.keterangan_naik.keterangan.toLowerCase().includes('sulit')">
                              <p class="text-xs text-error">Tinggi - Istirahat lebih
                                sering, peregangan lutut penting</p>
                            </template>
                          </div>
                        </div>

                        <!-- Kondisi Turun -->
                        <div class="rounded-lg border border-error/10 bg-error/5 p-3">
                          <div class="mb-2 flex items-center gap-2">
                            <x-gmdi-trending-down-r class="size-4 text-error" />
                            <span
                              class="text-xs font-semibold uppercase text-error">Kondisi
                              Turun</span>
                          </div>
                          <p class="text-sm font-semibold">
                            <span class="text-base-content/80">Beban Lutut: </span>
                            <span x-text="item.keterangan_turun.keterangan"
                              :class="{
                                  'text-success': item.keterangan_turun.keterangan.toLowerCase().includes('ringan'),
                                  'text-warning': item.keterangan_turun.keterangan.toLowerCase().includes('sedang'),
                                  'text-error': item.keterangan_turun.keterangan.toLowerCase().includes('berat') || item
                                      .keterangan_turun.keterangan.toLowerCase().includes('sulit')
                              }"></span>
                          </p>
                          <p class="text-xs text-base-content/60">Tingkat beban pada persendian lutut</p>
                        </div>
                      </div>

                      <!-- Tips Section -->
                      <div class="mt-4 rounded-lg border-l-4 border-primary bg-base-200/50 p-3">
                        <div class="flex items-start gap-2">
                          <x-gmdi-lightbulb-r
                            class="mt-0.5 size-4 flex-shrink-0 text-primary" />
                          <div>
                            <p class="mb-1 text-xs font-semibold text-primary">Tips
                              Pendakian:</p>
                            <p class="text-sm text-base-content/80">
                              <template x-if="typeof item.t === 'number' ? item.t > 25 : parseFloat(item.t) > 25">
                                <span>Cuaca panas, bawa air lebih banyak dan gunakan
                                  topi.</span>
                              </template>
                              <template x-if="typeof item.t === 'number' ? item.t <= 10 : parseFloat(item.t) <= 10">
                                <span>Cuaca dingin, gunakan pakaian berlapis dan sarung
                                  tangan.</span>
                              </template>
                              <template
                                x-if="typeof item.t === 'number' ? (item.t > 10 && item.t <= 25) : (parseFloat(item.t) > 10 && parseFloat(item.t) <= 25)">
                                <span>Cuaca sejuk, kondisi ideal untuk pendakian.</span>
                              </template>
                              <template x-if="typeof item.ws === 'number' ? item.ws > 20 : parseFloat(item.ws) > 20">
                                <span> Angin kencang, hati-hati di area terbuka.</span>
                              </template>
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template x-if="error && !loading">
        <div class="alert mx-auto max-w-screen-sm" role="alert">
          <x-gmdi-warning-r class="size-8 shrink-0 text-error" />
          <div>
            <h3 class="font-bold">Error!</h3>
            <div class="text-sm">Terjadi error saat mendapatkan informasi segmentasi jalur</div>
          </div>
          <button class="btn btn-sm" x-on:click="getSegmentasi">Refresh</button>
        </div>
      </template>
    </div>

    <h2 class="mx-auto mt-8 max-w-screen-lg text-center text-base-content/70">{{ $rute->deskripsi }}</h2>
  </div>

  <x-slot:js>
    <script title="alpine.js">
      let chart;
      let map;

      Chart.register(ChartDataLabels);

      function segmentasi() {
        return {
          segmentasi: {{ $rute->segmentasi }},
          loading: false,
          error: false,
          result: [],

          berat_beban_naik: this.$persist(15).as('berat-beban-naik'),
          berat_orang: this.$persist(75).as('berat-orang'),
          skala_waktu: this.$persist(1).as('skala-waktu'),
          segmentasi_active: 0,

          mapStyle: this.$persist('outdoor').as('map-style'),
          lngLatGunung: [{{ $rute->gunung->long }}, {{ $rute->gunung->lat }}],
          lngLatFirst: null,

          userId: @json(auth()->id()),

          init() {
            this.getSegmentasi();
            this.$watch('segmentasi_active', (val, old) => {
              if (val != old) {
                this.calculateChart();
                this.calculateMap()
              }
            })
            this.$watch('mapStyle', (val) => {
              if (val) {
                map.setStyle(
                  `https://api.maptiler.com/maps/${val}/style.json?key={{ env('MAP_STYLE_KEY') }}`
                )
                map.once('styledata', () => {
                  this.addTerrain();
                  this.calculateMap();
                });
              };
            })
            this.$watch('berat_beban_naik', (val) => {
              if (!val) this.berat_beban_naik = 15
            })
            this.$watch('berat_orang', (val) => {
              if (!val) this.berat_orang = 75
            })
            this.$watch('skala_waktu', (val) => {
              if (val === null || val === undefined) {
                this.skala_waktu = 1;
              }
            })
          },

          get segmentasi_data() {
            return this.result[this.segmentasi_active]
          },

          getSegmentasi() {
            this.loading = true;
            this.error = false;
            $.get({
              url: `/api/rute/{{ $rute->id }}/segmentasi?berat_beban_naik=${this.berat_beban_naik}&berat_orang=${this.berat_orang}&skala_waktu=${this.skala_waktu}`,
              success: (res) => {
                this.result = res;
                if (res.length) {
                  const firstPoint = this.result[0].points[0];
                  this.lngLatFirst = [+firstPoint.long, +firstPoint.lat]
                }
              },
              error: () => {
                this.error = true;
              },
              complete: () => {
                this.loading = false;
              }
            });
          },

          calculateChart() {
            const ctx = this.$refs.chartCanvas.getContext('2d');
            const curr = this.result[this.segmentasi_active] || [];

            let fullSegmentData = [];
            let distance = 0;

            let segmentEndIndices = [];
            let currentIndex = 0;

            this.result.forEach((segment, segIndex) => {
              segment.points.forEach((point, pointIndex) => {

                if (segIndex > 0 && pointIndex === 0) return;

                if (pointIndex > 0 || segIndex > 0) {
                  distance += 0.1;
                }

                fullSegmentData.push({
                  x: distance,
                  y: +point.elev,
                  segmentIndex: segIndex
                });

                currentIndex++;
              });

              segmentEndIndices.push(currentIndex - 1);
            });

            let currentSegmentData = [];
            let segmentStartIndex = 0;

            for (let i = 0; i < this.segmentasi_active; i++) {
              segmentStartIndex += this.result[i].points.length;

              if (i > 0) segmentStartIndex--;
            }

            for (let i = 0; i < curr.points.length; i++) {
              if (fullSegmentData[segmentStartIndex + i]) {
                currentSegmentData.push(fullSegmentData[segmentStartIndex + i]);
              }
            }

            const min = Math.min(...fullSegmentData.map(p => p.y)) - 100;
            const max = Math.max(...fullSegmentData.map(p => p.y)) + 100;

            const chartData = {
              datasets: [{
                  label: 'Segmentasi',
                  data: fullSegmentData,
                  backgroundColor: 'rgba(75, 192, 192, 0.2)',
                  borderColor: 'rgba(75, 192, 192, 0.6)',
                  borderWidth: 0.5,
                  tension: 0.2,
                  fill: true,
                  order: 2
                },
                {
                  label: 'Segmen Terpilih',
                  data: currentSegmentData,
                  backgroundColor: 'rgba(255, 99, 132, 0.4)',
                  borderColor: 'rgba(255, 99, 132, 1)',
                  borderWidth: 1.5,
                  tension: 0.1,
                  fill: false,
                  order: 1,
                  pointRadius: 3,
                  pointHoverRadius: 5
                }
              ]
            };

            const chartOptions = {
              responsive: true,
              maintainAspectRatio: false,
              layout: {
                padding: {
                  top: 30,
                  left: 20,
                  right: 20,
                  bottom: 20
                }
              },
              plugins: {
                tooltip: {
                  callbacks: {
                    title: function(tooltipItems) {
                      return '';
                    },
                    label: function(tooltipItem) {
                      return `${tooltipItem.raw.y} m`;
                    }
                  }
                },
                datalabels: {
                  display: function(context) {
                    const dataIndex = context.dataIndex;
                    return segmentEndIndices.includes(dataIndex);
                  },
                  color: 'black',
                  anchor: 'end',
                  align: 'top',
                  formatter: (value) => {
                    return `${value.y} m`;
                  },
                  font: {
                    weight: 'bold',
                    size: 8,
                  },
                },
                legend: {
                  display: true,
                  position: 'top',
                }
              },
              scales: {
                x: {
                  type: 'linear',
                  display: false,
                  title: {
                    display: false,
                    text: 'Distance'
                  },

                  min: Math.max(0, fullSegmentData[0]?.x - 0.3 || 0),
                  max: Math.min(distance + 0.3, fullSegmentData[fullSegmentData.length - 1]?.x + 0.3 ||
                    distance + 0.3)
                },
                y: {
                  min: min,
                  max: max,
                  display: false,
                  title: {
                    display: false,
                    text: 'Elevation (m)'
                  }
                }
              }
            };

            if (chart) {
              chart.data = chartData;
              chart.options.scales.y.min = min;
              chart.options.scales.y.max = max;
              chart.options.scales.x.min = Math.max(0, fullSegmentData[0]?.x - 0.3 || 0);
              chart.options.scales.x.max = Math.min(distance + 0.3, fullSegmentData[fullSegmentData.length - 1]
                ?.x + 0.3 ||
                distance + 0.3);
              chart.update();
            } else {
              chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: chartOptions
              });
            }
          },

          calculateMap() {
            const lngLats = this.result[this.segmentasi_active].line;

            const geojson = {
              'type': 'FeatureCollection',
              'features': [{
                'type': 'Feature',
                'geometry': {
                  'type': 'LineString',
                  'properties': {},
                  'coordinates': lngLats
                }
              }]
            };

            if (!map) {
              map = new maplibregl.Map({
                container: 'map',
                style: `https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`,
                center: this.lngLatGunung,
                zoom: 14,
              });

              map.on('load', () => {
                this.addTerrain();

                const bounds = new maplibregl.LngLatBounds();
                bounds.extend(this.lngLatGunung);
                bounds.extend(this.lngLatFirst);
                map.fitBounds(bounds, {
                  padding: 80,
                  maxZoom: 12,
                });

                this.mapUpdateLine(geojson);
              })
            } else {
              this.mapUpdateLine(geojson);
            }
          },

          addTerrain() {
            map.addSource('terrain', {
              type: 'raster-dem',
              url: `https://api.maptiler.com/tiles/terrain-rgb-v2/tiles.json?key={{ env('MAP_STYLE_KEY') }}`,
              tileSize: 256
            });

            map.setTerrain({
              source: 'terrain',
              exaggeration: 0.8
            });
          },

          mapUpdateLine(geojson) {
            if (map.getLayer('LineString')) {
              map.removeLayer('LineString');
            }

            if (map.getSource('LineString')) {
              map.removeSource('LineString');
            }

            map.addSource('LineString', {
              'type': 'geojson',
              'data': geojson
            });

            map.addLayer({
              'id': 'LineString',
              'type': 'line',
              'source': 'LineString',
              'layout': {
                'line-join': 'round',
                'line-cap': 'round'
              },
              'paint': {
                'line-color': '#0069ff',
                'line-width': 5
              }
            });

            const coordinates = geojson.features[0].geometry.coordinates;
            if (coordinates && coordinates.length > 0) {
              const bounds = new maplibregl.LngLatBounds();
              coordinates.forEach(coord => bounds.extend(coord));

              map.fitBounds(bounds, {
                padding: 80,
                maxZoom: 13
              });
            }
          }
        }
      }
    </script>

    <script type="module">
      import autoAnimate from "{{ asset('js/auto-animate.min.js') }}"
      $(function() {
        autoAnimate(document.getElementById('auto-animate-1'))
      })
    </script>
  </x-slot:js>

  <x-slot:head>
    <meta name="description" content="{{ $rute->deskripsi }}">
    <meta name="keywords"
      content="jalur pendakian, jalur pendakian gunung, rute pendakian, pendakian gunung, informasi jalur pendakian, jalur gunung">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url("/jalur-pendakian/{$rute->slug}/segmentasi") }}" hreflang="id" />
    <link rel="canonical" href="{{ url("/jalur-pendakian/{$rute->slug}/segmentasi") }}" />
    <meta property="og:type" content="website">
    <meta property="og:title"
      content="Jalur Pendakian Gunung {{ $rute->gunung->nama }} via {{ $rute->nama }} - Segmentasi">
    <meta property="og:description" content="{{ $rute->deskripsi }}">
    <meta property="og:url" content="{{ url("/jalur-pendakian/{$rute->slug}/segmentasi") }}">
    <meta property="og:site_name" content="muncak.id">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>
</x-layout.app>
