<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.role.index') }}">Role</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Role</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.role.create') }}">Tambah Role</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.role.index')" routeAction="/admin/api/role" name="role"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
