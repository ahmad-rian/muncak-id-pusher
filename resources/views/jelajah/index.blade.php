<x-layout.app>

  <x-slot:title>{{ 'Jelajahi Jalur Pendakian di Sekitarmu' }}</x-slot:title>

  @guest
    <dialog class="modal" id="auth-timeout-modal">
      <div class="modal-box">
        <h3 class="text-lg font-bold">Waktu Jelajah Anda Habis!</h3>
        <p class="py-4">Untuk melanjutkan menggunakan fitur ini, silakan daftar terlebih dahulu.</p>
        <div class="modal-action">
          <a class="btn btn-primary" href="{{ route('auth.sign-up') }}">Daftar Sekarang</a>
        </div>
      </div>
    </dialog>
  @endguest

  <main class="relative mt-[66px] flex justify-stretch" x-data="explore" x-init="init()">
    <div
      class="relative h-[calc(100vh-66px)] max-h-[calc(100vh-66px)] min-h-[calc(100vh-66px)] w-full shrink-0 grow-0 overflow-y-auto border-l border-base-300 bg-base-100 shadow transition-all lg:w-96"
      :class="open ? 'block' : 'hidden'" x-cloak>
      <div class="sticky inset-x-0 top-0 z-[1] bg-base-100 p-4 shadow">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <x-gmdi-terrain-r class="size-6" />
            <p class="font-merriweather font-semibold">Jalur Pendakian</p>
          </div>
        </div>
      </div>
      <div class="p-4">
        <button class="btn btn-sm btn-block mb-4 lg:hidden" x-on:click="open = false">
          <x-gmdi-map-r class="size-6" />
          Peta
        </button>

        <div id="auto-animate-1">
          <template x-if="data.length">
            <template x-for="item in data" :key="item.id">
              <a class="group card-compact block" :href="item.path" :class="item !== data.length - 1 && 'mb-4'">
                <figure>
                  <img class="h-48 w-full rounded-box object-cover object-center" :src="item.image"
                    alt="jalur-pendakian" />
                </figure>
                <div class="card-body !px-0">
                  <div>
                    <div class="line-clamp-1 font-merriweather text-lg font-bold group-hover:underline"
                      x-text="item.nama"></div>
                    <p class="line-clamp-1 text-base-content/70" x-text="item.lokasi"></p>
                    <div class="mt-1 line-clamp-1 flex flex-wrap items-center gap-2 text-sm text-base-content/90">
                      <template x-if="item.tingkat_kesulitan">
                        <div class="flex flex-wrap items-center gap-2">
                          <span x-text="item.tingkat_kesulitan"></span>
                          <span class="size-1 rounded-full bg-base-content/70"></span>
                        </div>
                      </template>
                      <template x-if="item.comment_count">
                        <div class="flex flex-wrap items-center gap-2">
                          <span class="flex items-center gap-0.5">
                            <x-gmdi-star-r class="size-3 text-yellow-500" />
                            <span x-text="item.comment_rating"></span>
                            <span x-text="'(' + item.comment_count + ')'"></span>
                          </span>
                          <span class="size-1 rounded-full bg-base-content/70"></span>
                        </div>
                      </template>
                      <template x-if="item.jarak_total">
                        <div class="flex flex-wrap items-center gap-2">
                          <span x-text="item.jarak_total"></span>
                          <span class="size-1 rounded-full bg-base-content/70"></span>
                        </div>
                      </template>
                      <template x-if="item.waktu_tempuh">
                        <div class="flex flex-wrap items-center gap-2">
                          <span x-text="item.waktu_tempuh"></span>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </a>
            </template>
          </template>
        </div>

        <template x-if="!data.length">
          <div class="py-4 text-center">
            <x-gmdi-image-not-supported-r class="mx-auto size-20 text-base-content/70" />
            <p class="mt-4 font-merriweather text-lg font-semibold">Hasil Tidak Ditemukan</p>
            <p class="mt-2 text-base-content/70">
              Tidak ada jalur pendakian di sekitar Anda. Coba interaksi dengan peta untuk menemukan lokasi lain.
            </p>
          </div>
        </template>
      </div>
    </div>

    <div class="h-[calc(100vh-66px)] max-h-[calc(100vh-66px)] min-h-[calc(100vh-66px)] w-full shrink grow"
      :class="open ? 'absolute inset-0 z-[-10] lg:z-[0] lg:relative' : ''">
      <div class="size-full" id="map" x-init="initMap"></div>
      <x-map-style-buttons />
      <div class="absolute left-4 top-0 z-[10] flex h-[56px] items-center justify-center">
        <div class="tooltip tooltip-right" :data-tip="open ? 'Tutup List' : 'Buka List'">
          <button class="btn btn-square btn-sm top-40 bg-base-100" x-on:click="open = !open">
            <span x-show="open" x-cloak>
              <x-gmdi-chevron-left-r class="size-6" />
            </span>
            <span x-show="!open" x-cloak>
              <x-gmdi-format-list-bulleted-r class="size-4" />
            </span>
          </button>
        </div>
      </div>
      <div class="relative z-[10]">
        <div class="absolute inset-x-0 bottom-6 z-[10] flex items-center justify-center" x-show="popupShow" x-transition
          x-cloak>
          <div class="h-28 w-full px-4 md:max-w-[24rem]">
            <a class="card card-side card-compact h-28 bg-base-100 shadow-lg" :href="popup.path"
              :title="popup.nama">
              <figure class="shrink-0 grow-0">
                <img class="h-full w-24 object-cover object-center" :src="popup.image" alt="jalur-pendakian" />
              </figure>
              <div class="card-body shrink grow justify-center">
                <div class="space-y-0.5">
                  <p class="line-clamp-1 font-merriweather text-sm font-semibold" x-text="popup.nama"></p>
                  <p class="line-clamp-1 text-xs text-base-content/70" x-text="popup.lokasi" :title="popup.lokasi">
                  </p>
                  <div class="line-clamp-1 flex flex-wrap items-center gap-2 text-sm text-base-content/90">
                    <template x-if="popup.tingkat_kesulitan">
                      <div class="flex flex-wrap items-center gap-2">
                        <span x-text="popup.tingkat_kesulitan"></span>
                        <span class="size-1 rounded-full bg-base-content/70"></span>
                      </div>
                    </template>
                    <template x-if="popup.comment_count">
                      <div class="flex flex-wrap items-center gap-2">
                        <span class="flex items-center gap-0.5">
                          <x-gmdi-star-r class="size-3 text-yellow-500" />
                          <span x-text="popup.comment_rating"></span>
                          <span x-text="'(' + popup.comment_count + ')'"></span>
                        </span>
                        <span class="size-1 rounded-full bg-base-content/70"></span>
                      </div>
                    </template>
                    <template x-if="popup.jarak_total">
                      <div class="flex flex-wrap items-center gap-2">
                        <span x-text="popup.jarak_total"></span>
                        <span class="size-1 rounded-full bg-base-content/70"></span>
                      </div>
                    </template>
                    <template x-if="popup.waktu_tempuh">
                      <div class="flex flex-wrap items-center gap-2">
                        <span x-text="popup.waktu_tempuh"></span>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="container mx-auto max-w-screen-md px-4 py-12 text-center md:px-6 xl:px-8">
    <h1 class="font-merriweather text-xl font-semibold">Jelajahi Jalur Pendakian di Sekitarmu</h1>
    <h2 class="mt-1 text-base-content/70">Jelajahi jalur pendakian gunung di sekitar lokasi Anda melalui peta
      interaktif
      yang menampilkan jalur pendakian
      lengkap dengan daftar rute yang dapat Anda pilih dan rencanakan perjalanan Anda.</h2>
  </div>

  <x-slot:js>
    <script>
      let map;

      function explore() {
        return {
          authTimeout: null,
          open: false,
          mapStyle: this.$persist('outdoor').as('map-style'),
          data: [],
          popupShow: false,
          bounds: null,
          popup: {
            nama: null,
            lokasi: null,
            tingkat_kesulitan: null,
            rating: null,
            jarak_total: null,
            waktu_tempuh: null,
            path: null,
            image: null,
          },
          init() {
            @guest
            this.authTimeout = setTimeout(() => {
              document.getElementById('auth-timeout-modal').showModal();
            }, 60 * 1000);
          @endguest
        },
        initMap() {
            map = new maplibregl.Map({
              container: 'map',
              style: `https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`,
              center: [106.827153, -6.175392],
              zoom: 8,
            });
            map.on('load', async () => {
              this.getRute();
              this.addTerrain();
              const debouncedGetRute = _.debounce(() => this.getRute(), 300);
              map.on('moveend', debouncedGetRute);
              map.on('zoomend', debouncedGetRute);
            });
            this.$watch('mapStyle', (val) => {
              if (val) {
                map.setStyle(`https://api.maptiler.com/maps/${val}/style.json?key={{ env('MAP_STYLE_KEY') }}`)
                map.once('styledata', () => {
                  this.addTerrain();
                  this.getRute();
                });
              };
            })
          },

          getRute() {
            const bounds = map.getBounds();
            const minLng = bounds.getWest();
            const maxLng = bounds.getEast();
            const minLat = bounds.getSouth();
            const maxLat = bounds.getNorth();

            this.bounds = bounds

            $.ajax({
              url: "{{ route('api.jelajah.rute') }}",
              method: 'GET',
              dataType: 'json',
              data: {
                minLng: minLng,
                maxLng: maxLng,
                minLat: minLat,
                maxLat: maxLat
              },
              success: (data) => {
                this.data = data;
                this.setCluster();
              },
              error: (jqXHR, textStatus, errorThrown) => {
                console.error("Request failed: " + textStatus + ", " + errorThrown);
                alert("An error occurred while fetching data. Please try again later.");
              }
            });
          },

          setCluster() {
            const geojson = {
              type: "FeatureCollection",
              features: this.data.map(route => {
                const firstPoint = JSON.parse(route.point[0].point_geo);
                return {
                  type: "Feature",
                  properties: {
                    ruteId: route.id,
                    nama: route.nama,
                    lokasi: route.lokasi,
                    tingkat_kesulitan: route.tingkat_kesulitan,
                    rating: route.rating,
                    jarak_total: route.jarak_total,
                    waktu_tempuh: route.waktu_tempuh,
                    comment_rating: route.comment_rating,
                    comment_count: route.comment_count,
                    path: route.path,
                    image: route.image,
                  },
                  geometry: firstPoint
                };
              })
            };

            if (map.getSource('first-point')) {
              map.getSource('first-point').setData(geojson);
            } else {
              map.addSource('first-point', {
                type: 'geojson',
                data: geojson,
                cluster: true,
                clusterMaxZoom: 14,
                clusterRadius: 50
              });

              map.addLayer({
                id: 'clusters',
                type: 'circle',
                source: 'first-point',
                filter: ['has', 'point_count'],
                paint: {
                  'circle-color': '#0069ff',
                  'circle-radius': ['step', ['get', 'point_count'], 15, 10, 20]
                }
              });

              map.addLayer({
                id: 'cluster-count',
                type: 'symbol',
                source: 'first-point',
                filter: ['has', 'point_count'],
                layout: {
                  'text-field': '{point_count_abbreviated}',
                  'text-size': 12,
                },
                paint: {
                  'text-color': '#ffffff',
                }
              });

              map.addLayer({
                id: 'unclustered-point',
                type: 'circle',
                source: 'first-point',
                filter: ['!', ['has', 'point_count']],
                paint: {
                  'circle-color': '#0069ff',
                  'circle-radius': 10
                }
              });

              map.on('click', 'unclustered-point', (e) => this.handleClick(e));
              map.on('zoomstart', () => this.removePopupAndRoute());
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
              exaggeration: 1.5
            });
          },

          handleClick(e) {
            this.removePopupAndRoute();

            const features = map.queryRenderedFeatures(e.point, {
              layers: ['unclustered-point']
            });

            if (features.length) {
              const feature = features[0];
              const ruteId = feature.properties.ruteId;
              const route = this.data.find(item => item.id === ruteId);

              if (route && route.rute_geo) {
                const ruteGeo = JSON.parse(route.rute_geo);

                map.addSource('route', {
                  type: 'geojson',
                  data: ruteGeo
                });

                map.addLayer({
                  id: 'route-line',
                  type: 'line',
                  source: 'route',
                  paint: {
                    'line-color': '#0069ff',
                    'line-width': 3
                  }
                });
              }

              this.popup.nama = feature.properties.nama;
              this.popup.lokasi = feature.properties.lokasi;
              this.popup.tingkat_kesulitan = feature.properties.tingkat_kesulitan;
              this.popup.rating = feature.properties.rating;
              this.popup.jarak_total = feature.properties.jarak_total;
              this.popup.waktu_tempuh = feature.properties.waktu_tempuh;
              this.popup.comment_rating = feature.properties.comment_rating;
              this.popup.comment_count = feature.properties.comment_count;
              this.popup.path = feature.properties.path;
              this.popup.image = feature.properties.image;
              this.popupShow = true;
            }
          },

          removePopupAndRoute() {
            this.popupShow = false;
            if (map.getSource('route')) {
              map.removeLayer('route-line');
              map.removeSource('route');
            }
          },

          destroy() {
            @guest
            // Clear timeout when component is destroyed
            if (this.authTimeout) {
              clearTimeout(this.authTimeout);
            }
          @endguest
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
    <meta name="description"
      content="Jelajahi jalur pendakian gunung di sekitar lokasi Anda melalui peta interaktif yang menampilkan jalur pendakian lengkap dengan daftar rute yang dapat Anda pilih dan rencanakan perjalanan Anda.">
    <meta name="keywords"
      content="jalur pendakian, jalur pendakian gunung, peta jalur pendakian, jelajahi jalur pendakian, rute pendakian gunung, peta interaktif, rute pendakian">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url('/jelajah') }}" hreflang="id" />
    <link rel="canonical" href="{{ url('/jelajah') }}" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="Jelajahi Jalur Pendakian di Sekitarmu">
    <meta property="og:description"
      content="Jelajahi jalur pendakian gunung di sekitar lokasi Anda melalui peta interaktif yang menampilkan jalur pendakian lengkap dengan daftar rute yang dapat Anda pilih dan rencanakan perjalanan Anda.">
    <meta property="og:url" content="{{ url('/jelajah') }}">
    <meta property="og:site_name" content="muncak.id">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>
</x-layout.app>
