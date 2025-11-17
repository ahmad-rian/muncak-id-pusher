<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.negara.index') }}">Negara</a></li>
      <li>Detail</li>
      <li>{{ $data->nama }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Detail Negara</p>
    <div class="flex gap-2">
      <a class="btn btn-warning btn-sm" href="{{ route('admin.negara.edit', $data->id) }}">Edit</a>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.negara.index') }}">Kembali</a>
    </div>
  </div>

  <div class="mt-6 overflow-x-auto">
    <table class="table table-zebra">
      <tbody>
        <tr>
          <td class="font-semibold">Nama</td>
          <td>{{ $data->nama }}</td>
        </tr>
        <tr>
          <td class="font-semibold">Slug</td>
          <td>{{ $data->slug }}</td>
        </tr>
        <tr>
          <td class="font-semibold">Nama Lain</td>
          <td>{{ $data->nama_lain ?? '-' }}</td>
        </tr>
        <tr>
          <td class="font-semibold">Kode</td>
          <td>{{ $data->kode ?? '-' }}</td>
        </tr>
        <tr>
          <td class="font-semibold">Dibuat</td>
          <td>{{ $data->created_at->format('d M Y H:i') }}</td>
        </tr>
        <tr>
          <td class="font-semibold">Diperbarui</td>
          <td>{{ $data->updated_at->format('d M Y H:i') }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <x-slot:js>
    <script title="alpine.js"></script>
  </x-slot:js>
</x-layout.admin>