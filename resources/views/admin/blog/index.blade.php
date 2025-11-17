<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.blog.index') }}">Artikel</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Artikel</p>
    <a class="btn btn-neutral btn-sm" href="{{ route('admin.blog.create') }}">Tambah Artikel</a>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.blog.index')" routeAction="/admin/api/blog" name="blog"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
