@props(['data'])

<div x-data="table" x-init="tabs == 'point' && initComponent()">
  <div class="mb-4 h-60 w-full overflow-hidden rounded-md" x-cloak x-show="mapOpen" x-cloak x-transition>
    <div class="size-full" id="map"></div>
  </div>

  <div x-init="@error('file') update = true @enderror">
    <div class="rounded-lg border border-base-300" x-cloak x-show="update">
      <div class="card card-compact">
        <form class="card-body" method="post" x-data="pointForm" x-on:submit.prevent="submit"
          action="{{ route('admin.rute.store-point', $data) }}" enctype="multipart/form-data"
          :class="submitting && 'pointer-events-none select-none'">
          @csrf

          <div class="alert whitespace-normal rounded-md" x-cloak x-show="!getCount">
            <x-gmdi-info-r class="size-6 text-info" />
            <div>
              <p class="text-base font-semibold">Belum ada data titik pada rute ini!</p>
              <p class="text-sm">
                Silakan unggah file dengan ekstensi .xlsx dalam format UTM atau Geografis (Latitude/Longitude).
              </p>
            </div>
          </div>

          <div class="alert whitespace-normal rounded-md" x-cloak x-show="getCount">
            <x-gmdi-warning-r class="size-6 text-warning" />
            <div>
              <p class="text-base font-semibold">Perhatian: Data Titik Saat Ini Akan Dihapus!</p>
              <p class="text-sm">
                Memperbarui data titik pada rute ini akan menghapus semua titik yang sudah ada.
              </p>
            </div>
          </div>

          <div class="mt-4">
            <div>
              <p class="text-base font-semibold">Syarat Mengunggah File Titik</p>
              <ul class="ml-4 mt-2 list-outside list-disc">
                <li>
                  File harus berekstensi .xlsx.
                </li>
                <li>
                  Header Excel harus memiliki kolom X, Y, dan Z.
                </li>
                <li>
                  X sebagai latitude, Y sebagai longitude dan Z sebagai elevasi.
                </li>
                <li>
                  Isi kolom X, Y, dan Z harus berformat number atau angka.
                </li>
              </ul>
            </div>

            <div class="mt-4 overflow-hidden overflow-x-auto rounded-md border border-base-300">
              <table class="table table-zebra table-sm">
                <tr>
                  <th width="5%">#</th>
                  <th>x</th>
                  <th>y</th>
                  <th>z</th>
                </tr>
                <tr class="hover">
                  <td>1</td>
                  <td>-8.287754</td>
                  <td>116.412581</td>
                  <td>365.41</td>
                </tr>
                <tr class="hover">
                  <td>2</td>
                  <td>-8.287870</td>
                  <td>116.412514</td>
                  <td>365.80</td>
                </tr>
                <tr class="hover">
                  <td>3</td>
                  <td>-8.288652</td>
                  <td>116.412060</td>
                  <td>375.17</td>
                </tr>
                <tr class="hover">
                  <td colspan="4">
                    <p class="text-center">...</p>
                  </td>
                </tr>
              </table>
            </div>

            <div class="mt-4">
              <div class="label">
                <label class="label-text" for="file">File (.xlxs)</label>
              </div>
              <input
                class="@error('file') {{ 'file-input-error' }} @enderror file-input file-input-bordered file-input-sm w-full"
                id="file" type="file" name="file" accept=".xlsx" required>
              @error('file')
                <div class="label">
                  <span class="label-text-alt text-error">{{ $message }}</span>
                </div>
              @enderror
            </div>
          </div>

          <div class="card-actions mt-4 flex-row-reverse">
            <button class="btn-submit btn btn-success btn-sm" type="submit">Submit</button>
            <button class="btn-submit btn btn-sm" type="button" x-cloak x-show="getCount" x-on:click="update = false">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="mt-4 rounded-lg border border-base-300" x-cloak x-show="getCount">
      <div class="flex flex-col items-center justify-between gap-x-2 gap-y-4 border-b border-base-300 p-4 lg:flex-row">
        <div class="flex justify-center gap-2 lg:justify-start">
          <button class="btn btn-neutral btn-sm shrink-0 grow-0" x-on:click="update = !update">
            Perbarui Titik
          </button>
          <button class="btn btn-square btn-sm" :class="mapOpen && 'btn-active'" x-on:click="mapOpen = !mapOpen">
            <x-gmdi-route-r class="size-6" />
          </button>
        </div>

        <div class="flex shrink grow flex-col items-center justify-end gap-x-2 gap-y-4 md:flex-row">
          <span class="loading loading-spinner" x-cloak x-show="loading"></span>
          <div class="join shrink-0 grow-0">
            <p class="btn join-item btn-sm">Per Page:</p>
            <select class="join-item select select-bordered select-sm" id="take" x-model="take">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>

          <label class="input input-sm input-bordered flex w-full max-w-sm shrink items-center gap-2" for="s">
            <x-gmdi-search-r class="size-5" />
            <input class="grow" id="s" type="text" name="s" placeholder="Search..." />
          </label>
        </div>
      </div>

      <div class="overflow-x-auto">
        <div class="min-w-[40rem]" :class="loading && 'opacity-35 pointer-events-none'" x-html="data"></div>
      </div>

      <div class="flex flex-col items-center justify-between gap-2 border-t border-base-300 p-4 md:flex-row">
        <p class="text-sm text-base-content/70">
          Showing <span x-text="(page - 1) * take + 1"></span>
          to <span x-text="Math.min(page * take, count);"></span>
          of <span x-text="count"></span>
          results
        </p>

        <div class="join shrink-0 grow-0">
          <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page--" :disabled="page == 1">
            «
          </button>
          <template x-for="item in pagination">
            <button class="btn join-item btn-xs sm:btn-sm" :disabled="!item" :class="page == item && 'btn-active'"
              x-on:click="page = item" x-text="item || '..'">
            </button>
          </template>
          <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page++"
            :disabled="page == Math.ceil(count / take)">
            »
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script title="alpine.js">
  var map;

  function table() {
    return {
      initComponent() {
        this.getData();
        this.$watch('page', () => {
          this.getData();
        });
        this.$watch('take', () => {
          if (this.page == 1) this.getData();
          else this.page = 1;
        });
        this.$watch('mapOpen', (val) => {
          if (val) this.getPoints();
        })
      },

      data: [],
      loading: false,
      error: false,
      count: 0,
      page: 1,
      take: 10,
      order: null,
      direction: 'desc',
      update: false,
      _token: $('meta[name="csrf-token"]').attr('content'),

      get getCount() {
        return this.count;
      },

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

      getData() {
        this.loading = true;
        $.ajax({
          url: "{{ route('admin.api.rute.point', $data) }}",
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            _token: this._token,
            take: this.take,
            page: this.page,
            order: this.order,
            direction: this.direction,
          }),
          success: (data) => {
            this.data = data;
            this.$nextTick(() => {
              if (!this.count) this.update = true;
            })
          },
          error: (error) => {
            this.error = true;
          },
          complete: () => {
            this.loading = false;
          }
        })
      },

      sortData(order) {
        this.page = 1;
        if (this.order == order) {
          this.direction = this.direction == 'asc' ? 'desc' : 'asc';
        } else {
          this.order = order;
          this.direction = 'asc';
        }
        this.getData();
      },

      updateData(id) {
        this.loading = true;
        const $el = $(this.$el);
        const name = $el.data('name');
        let value = "";
        if ($el.attr('type') === 'checkbox') {
          value = $el.is(':checked');
        } else if ($el.attr('type') === 'text') {
          value = $el.val();
        }

        $.ajax({
          url: `/admin/api/point/${id}`,
          method: "PUT",
          contentType: 'application/json',
          data: JSON.stringify({
            _token: this._token,
            [name]: value,
          }),
          success: (data) => {
            Alpine.store('toast').push({
              type: data.toast.type,
              title: data.toast.title,
              message: data.toast.message,
            })
          },
          error: (error) => {
            this.error = true;
          },
          complete: () => {
            this.loading = false;
          }
        })
      },

      mapOpen: false,
      mapStyle: this.$persist('outdoor').as('map-style'),
      lineString: [],

      getPoints() {
        $.get({
          url: "{{ route('admin.api.rute.points', $data) }}",
          contentType: 'application/json',
          success: (data) => {
            if (!data) return;
            this.lineString = JSON.parse(data);
            this.initMap([106.827153, -6.175392]);
            this.renderLineString();
          }
        });
      },

      renderLineString() {
        if (!this.lineString || !this.lineString.coordinates) {
          console.error("Invalid LineString data.");
          this.mapOpen = false;
          return;
        }

        map.on('load', () => {
          map.addSource('route', {
            type: 'geojson',
            data: this.lineString,
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
          this.lineString.coordinates.forEach(coord => {
            bounds.extend(coord);
          });

          map.fitBounds(bounds, {
            padding: 50,
            maxZoom: 24,
            duration: 2000,
          });
        });
      },

      initMap(lngLat) {
        map = new maplibregl.Map({
          container: 'map',
          style: `https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`,
          center: lngLat,
          zoom: 14,
        });
      },
    }
  }

  function pointForm() {
    return {
      submitting: false,
      submit() {
        this.submitting = true;
        Alpine.store('toast').push({
          type: 'info',
          title: 'Info: Points Are Being Processed!',
          message: 'The points are being processed soon. Please do not reload the page or navigate away.'
        });

        setTimeout(() => {
          this.$el.submit();
        }, 1000);
      }
    }
  }
</script>
