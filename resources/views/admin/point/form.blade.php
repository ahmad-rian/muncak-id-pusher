<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.gunung.index') }}">Point</a></li>
      <li><a href="{{ route('admin.rute.show', $data->rute) }}">Rute {{ $data->rute_id }}</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->id }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Point</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post" enctype="multipart/form-data">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <x-form.input name="nama" label="Nama" :value="old('nama', $data->nama)"></x-form.input>
      </div>

      <div class="md:col-span-2">
        <x-form.textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)"></x-form.textarea>
      </div>

      <div class="md:col-span-2">
        <x-form.image-multiple name="gallery" label="Gallery: Max 2 Images (jpg, jpeg, png, webp)" :value="$data->getGalleryUrls()" />
      </div>

      <div class="flex items-center gap-1">
        <input class="checkbox checkbox-sm shrink-0 grow-0" id="is_lokasi_prediksi_cuaca" value="1" type="checkbox"
          name="is_lokasi_prediksi_cuaca" @checked(old('is_lokasi_prediksi_cuaca', $data->is_lokasi_prediksi_cuaca)) />
        <div class="label">
          <label class="label-text" for="is_lokasi_prediksi_cuaca">Lokasi Prediksi Cuaca</label>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <input class="checkbox checkbox-sm shrink-0 grow-0" id="is_waypoint" value="1" type="checkbox"
          name="is_waypoint" @checked(old('is_waypoint', $data->is_waypoint)) />
        <div class="label">
          <label class="label-text" for="is_waypoint">Waypoint</label>
        </div>
      </div>
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.rute.show', $data->rute) }}">Cancel</a>
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
      // 
    </script>
  </x-slot:js>
</x-layout.admin>
