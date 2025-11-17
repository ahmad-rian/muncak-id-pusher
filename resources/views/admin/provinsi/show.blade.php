<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.provinsi.index') }}">Provinsi</a></li>
      <li>Show</li>
      <li>{{ $data->kode }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Provinsi</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th width="15%">Kode</th>
          <td>{{ $data->kode }}</td>
        </tr>
        <tr>
          <th>Nama</th>
          <td>{{ $data->nama }}</td>
        </tr>
        <tr>
          <th>Nama Lain</th>
          <td>{{ $data->nama_lain }}</td>
        </tr>
        <tr>
          <th>Slug</th>
          <td>{{ $data->slug }}</td>
        </tr>
        <tr>
          <th>Timezone</th>
          <td>{{ $data->timezone }}</td>
        </tr>
        <tr>
          <th>Latitude</th>
          <td>{{ $data->lat }}</td>
        </tr>
        <tr>
          <th>Longitude</th>
          <td>{{ $data->long }}</td>
        </tr>
        <tr>
          <th>Updated At</th>
          <td>{{ $data->created_at }}</td>
        </tr>
        <tr>
          <th>Created At</th>
          <td>{{ $data->updated_at }}</td>
        </tr>
      </table>
    </div>
  </div>

  <x-slot:js>
    <script title="alpine.js">
      // 
    </script>
  </x-slot:js>
</x-layout.admin>
