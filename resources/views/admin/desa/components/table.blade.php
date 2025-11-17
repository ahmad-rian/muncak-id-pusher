<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="15%">Kode</td>
        <td>Nama</td>
        <td>Nama Lain</td>
        <td>Timezone</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $item->kode }}</td>
          <td>{{ $item->nama }}</td>
          <td>{{ $item->nama_lain ?? '-' }}</td>
          <td>{{ $item->timezone ?? '-' }}</td>
          <td>
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.desa.edit', $item->kode) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.desa.show', $item->kode) }}">
                Lihat
              </a>
              <button class="btn btn-error btn-xs" x-on:click="deleteAction('{{ $item->kode }}')">
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
