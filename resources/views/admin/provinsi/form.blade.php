<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.provinsi.index') }}">Provinsi</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->kode }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Provinsi</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-data="namaSlug">
      <div>
        <div class="label">
          <label class="required label-text" for="nama">
            Nama
          </label>
        </div>
        <input class="@error('nama') {{ 'input-error' }} @enderror input input-sm input-bordered w-full" id="nama"
          required x-model.debounce.500ms="nama" type="text" placeholder="Nama" name="nama" required />
        @error('nama')
          <div class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
          </div>
        @enderror
      </div>

      <div>
        <div class="label">
          <label class="required label-text" for="slug">
            Slug
          </label>
        </div>
        <input class="@error('slug') {{ 'input-error' }} @enderror input input-sm input-bordered w-full" id="slug"
          required x-model="slug" type="text" placeholder="Slug" name="slug" required />
        @error('slug')
          <div class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
          </div>
        @enderror
      </div>

      <x-form.input type="text" name="kode" label="Kode Provinsi" :value="old('kode', $data->kode)" required />
      <x-form.input type="text" name="nama_lain" label="Nama Lain" :value="old('nama_lain', $data->nama_lain)" required />
      <x-form.input type="text" name="timezone" label="Timezone" :value="old('timezone', $data->timezone)" />

      <div class="md:col-span-2" x-data="latLong">
        <div class="label">
          <label class="label-text" for="lat">Latitude Longitude</label>
          <label class="label-text-alt text-primary" for="lat">
            Double-click on the map to set latitude and longitude.
          </label>
        </div>
        <div class="relative h-48 overflow-hidden rounded-md border border-dashed border-base-content/30">
          <div class="size-full" id="map"></div>
          <x-map-style-buttons />
        </div>
        <div class="mt-2 grid grid-cols-2 gap-2">
          <div>
            <input class="@error('lat') {{ 'file-input-error' }} @enderror input input-sm input-bordered w-full"
              id="lat" x-model.lazy.number="lat" type="text" name="lat" placeholder="Latitude" />
            @error('lat')
              <div class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </div>
            @enderror
          </div>

          <div>
            <input class="@error('long') {{ 'file-input-error' }} @enderror input input-sm input-bordered w-full"
              id="long" x-model.lazy.number="long" type="text" name="long" placeholder="Longitude" />
            @error('long')
              <div class="label">
                <span class="label-text-alt text-error">{{ $message }}</span>
              </div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.provinsi.index') }}">Cancel</a>
    </div>

    @if ($errors->any())
      <div class="alert alert-error mt-4">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </form>

  <x-slot:js>
    <script title="alpine.js">
      function namaSlug() {
        return {
          nama: "{{ old('nama', $data->nama) }}",
          slug: "{{ old('slug', $data->slug) }}",
          init() {
            this.$watch('nama', () => {
              this.slug = this.nama.toString()
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            })
          }
        }
      }

      let map;

      function latLong() {
        return {
          lat: @json(old('lat', $data->lat)),
          long: @json(old('long', $data->long)),
          mapStyle: this.$persist('outdoor').as('map-style'),
          marker: null,
          init() {
            const lngLat = [this.long || 106.827153, this.lat || -6.175392];
            this.initMap(lngLat);
            this.handleDoubleClick();
            this.$watch('mapStyle', this.changeMapStyle);
          },
          initMap(lngLat) {
            map = new maplibregl.Map({
              container: 'map',
              style: `https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`,
              center: lngLat,
              zoom: 14,
            });
            this.addMarker({
              lat: this.lat,
              lng: this.long
            });
          },
          changeMapStyle() {
            map.setStyle(`https://api.maptiler.com/maps/${this.mapStyle}/style.json?key={{ env('MAP_STYLE_KEY') }}`);
          },
          handleDoubleClick() {
            map.on('dblclick', (event) => {
              const {
                lat,
                lng
              } = event.lngLat;
              this.addMarker({
                lat,
                lng
              });
              this.lat = lat;
              this.long = lng;
            });
          },
          addMarker({
            lat,
            lng
          }) {
            if (this.marker) {
              this.marker.setLngLat([lng, lat]);
            } else {
              this.marker = new maplibregl.Marker()
                .setLngLat([lng, lat])
                .addTo(map);
            }
          },
        };
      }
    </script>
  </x-slot:js>
</x-layout.admin>
