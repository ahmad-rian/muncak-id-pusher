<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td width="10%">Gambar</td>
        <td>Judul & Deskripsi</td>
        <td>Publish</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>
            <img class="size-10 rounded-sm object-cover object-center" src="{{ $item->getImageUrl() }}"
              alt="Cover Image" />
          </td>
          <td>
            <p class="font-medium">{{ $item->title }}</p>
            <p class="mt-1 text-sm text-base-content/70">{{ $item->deskripsi_singkat }}</p>
          </td>
          <td>
            <label class="label justify-start gap-1 py-0" for="{{ "is_published_{$item->id}" }}">
              <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_published_{$item->id}" }}"
                data-name="is_published" x-on:change="updateData({{ $item->id }})" value="1" type="checkbox"
                name="{{ "is_published_{$item->id}" }}" @checked($item->is_published) />
            </label>
          </td>
          <td>
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.blog.edit', $item) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.blog.show', $item) }}">
                Lihat
              </a>
              <button class="btn btn-error btn-xs" x-on:click="deleteAction({{ $item->id }})">
                Hapus
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  @else
    <tr>
      <td>
        <div class="flex flex-col items-center py-8 text-base-content/70">
          <x-gmdi-folder-off-r class="size-16" />
          <p>No Data Available</p>
        </div>
      </td>
    </tr>
  @endif
</table>
