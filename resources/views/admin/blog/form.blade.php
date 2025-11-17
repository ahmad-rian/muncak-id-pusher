<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.blog.index') }}">Artikel</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->id }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Artikel</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post" enctype="multipart/form-data">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-12 gap-4">
      <div class="col-span-full lg:col-span-4 space-y-2 lg:order-last">
        <x-form.textarea name="title" label="Judul Blog" :value="old('title', $data->title)" required></x-form.textarea>

        <div class="flex items-center gap-1">
          <input class="checkbox checkbox-sm shrink-0 grow-0" id="is_published" value="1" type="checkbox"
            name="is_published" @checked(old('is_published', $data->is_published)) />
          <div class="label">
            <label class="label-text" for="is_published">Apakah Anda ingin mempublikasikan blog ini?</label>
          </div>
        </div>

        <x-form.input name="slug" label="Slug atau Tautan" placeholder="Contoh: rekomendasi-gunung-ramah-pemula"
          :value="old('slug', $data->slug)" required></x-form.input>

        <x-form.textarea name="deskripsi_singkat" label="Deskripsi Singkat" :value="old('deskripsi_singkat', $data->deskripsi_singkat)"></x-form.textarea>

        <x-form.image name="image" label="Image Cover (jpg, jpeg, png, webp)" :value="$data->getImageUrl() ? $data->getImageUrl() : ''"></x-form.image>

      </div>

      <div class="col-span-full lg:col-span-8">
        <x-form.text-editor class="h-[40rem]" name="content" label="Konten Blog" :value="old('content', $data->content)"></x-text-editor>
      </div>
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.blog.index') }}">Cancel</a>
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
    <script>
      document.addEventListener('trix-initialize', function(event) {
        const editor = event.target;
        const toolbar = editor.toolbarElement;
        toolbar.querySelector('.trix-button-group--file-tools').style.display = "block";
      });
    </script>
  </x-slot:js>
</x-layout.admin>
