<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.rute-tingkat-kesulitan.index') }}">Rute Tingkat Kesulitan</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Rute Tingkat Kesulitan</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.rute-tingkat-kesulitan.create') }}">Tambah Rute Tingkat Kesulitan</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.rute-tingkat-kesulitan.index')" routeAction="/admin/api/rute-tingkat-kesulitan" name="ruteTingkatKesulitan"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
