<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.role.index') }}">Role</a></li>
      <li>Show</li>
      <li>{{ $data->kode }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Role</p>
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
          <td>{{ $data->name }}</td>
        </tr>
        <tr>
          <th>Guard Name</th>
          <td>{{ $data->guard_name }}</td>
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
