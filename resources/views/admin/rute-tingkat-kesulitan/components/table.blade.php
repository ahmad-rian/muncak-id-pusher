<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td>Nama</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>{{ $item->nama }}</td>
          <td>
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.rute-tingkat-kesulitan.edit', $item) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.rute-tingkat-kesulitan.show', $item) }}">
                Lihat
              </a>
              <button class="btn btn-error btn-xs" x-on:click="deleteAction('{{ $item->id }}')">
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
