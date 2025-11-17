<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.role.index') }}">Rute Tingkat Kesulitan</a></li>
      <li>Show</li>
      <li>{{ $data->id }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Tingkat Kesulitan</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th width="15%">ID</th>
          <td>{{ $data->id }}</td>
        </tr>
        <tr>
          <th>Nama</th>
          <td>{{ $data->nama }}</td>
        </tr>
        <tr>
          <th>Slug</th>
          <td>{{ $data->slug }}</td>
        </tr>
        <tr>
          <th>Deskripsi</th>
          <td>{{ $data->deskripsi }}</td>
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
