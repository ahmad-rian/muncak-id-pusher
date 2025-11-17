<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.kabupaten-kota.index') }}">Kabupaten Kota</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Kabupaten Kota</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.kabupaten-kota.create') }}">Tambah Kabupaten Kota</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.kabupaten-kota.index')" routeAction="/admin/api/kabupaten-kota" name="kabupatenKota"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
