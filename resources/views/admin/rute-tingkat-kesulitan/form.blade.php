<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.rute-tingkat-kesulitan.index') }}">Rute Tingkat Kesulitan</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->kode }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Rute Tingkat Kesulitan</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="md:col-span-2">
        <x-form.input name="nama" label="Nama" :value="old('nama', $data->nama)" required />
      </div>

      <div class="md:col-span-2">
        <x-form.textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)"></x-form.textarea>
      </div>
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.rute-tingkat-kesulitan.index') }}">Cancel</a>
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
