<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.provinsi.index') }}">Provinsi</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Provinsi</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.provinsi.create') }}">Tambah Provinsi</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.provinsi.index')" routeAction="/admin/api/provinsi" name="provinsi"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
