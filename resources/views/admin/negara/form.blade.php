<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.negara.index') }}">Negara</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->nama }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Negara</p>
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

      <x-form.input type="text" name="nama_lain" label="Nama Lain" :value="old('nama_lain', $data->nama_lain)" />
      <x-form.input type="text" name="kode" label="Kode Negara" :value="old('kode', $data->kode)" />
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.negara.index') }}">Cancel</a>
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
    </script>
  </x-slot:js>
</x-layout.admin>