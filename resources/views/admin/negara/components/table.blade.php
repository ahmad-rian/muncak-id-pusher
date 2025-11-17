<div class="overflow-x-auto">
  <table class="table table-zebra">
    <thead>
      <tr>
        <th>Kode</th>
        <th>Nama</th>
        <th>Nama Lain</th>
        <th width="15%">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($data as $item)
        <tr>
          <td>{{ $item->kode ?? '-' }}</td>
          <td>{{ $item->nama }}</td>
          <td>{{ $item->nama_lain ?? '-' }}</td>
          <td>
            <div class="flex gap-2">
              <a class="btn btn-success btn-xs" href="{{ route('admin.negara.edit', $item->id) }}">Edit</a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.negara.show', $item->id) }}">Lihat</a>
              <button class="btn btn-error btn-xs" x-on:click="deleteAction({{ $item->id }})">Hapus</button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-center">Tidak ada data</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>