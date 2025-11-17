<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.kecamatan.index') }}">Kecamatan</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Kecamatan</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.kecamatan.create') }}">Tambah Kecamatan</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.kecamatan.index')" routeAction="/admin/api/kecamatan" name="kecamatan"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
