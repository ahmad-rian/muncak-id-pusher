<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.gunung.index') }}">Gunung</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->id }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Gunung</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post" enctype="multipart/form-data">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <x-form.input name="nama" label="Nama Gunung" :value="old('nama', $data->nama)" required></x-form.input>
      </div>

      <div>
        <x-form.input type="text" name="elev" label="Elevasi/Ketinggian (m)" :value="old('elev', $data->elev)"
          required></x-form.input>
      </div>

      <div>
        <x-form.input name="lokasi" label="Lokasi" :value="old('lokasi', $data->lokasi)" placeholder="Contoh: Taman Nasional Gunung Rinjani"></x-form.input>
      </div>

      <div>
        <x-form.select :route="route('admin.api.negara.select')" name="negara_id" label="Negara"
          :value="old('negara_id', $data->negara_id)"></x-form.select>
      </div>

      <div>
        <x-form.select :route="route('admin.api.kabupaten-kota.select')" name="kode_kabupaten_kota" label="Kabupaten/Kota"
          :value="old('kode_kabupaten_kota', $data->kode_kabupaten_kota)"></x-form.select>
      </div>

      <div class="md:col-span-2">
        <x-form.textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)"></x-form.textarea>
      </div>

      <div class="md:col-span-2">
        <x-form.image name="image" label="Image Cover (jpg, jpeg, png, webp)" :value="$data->getImageUrl() ? $data->getImageUrl() : ''"></x-form.image>
      </div>

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
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.gunung.index') }}">Cancel</a>
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
