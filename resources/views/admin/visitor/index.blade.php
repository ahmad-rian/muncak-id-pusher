<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.visitor.index') }}">Visitor</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Visitor</p>
  </div>

  <form class="tooltip mt-4 text-center tooltip-right" data-tip="Hapus pengunjung lama dan pertahankan data 4 minggu terakhir"
    action="{{ route('admin.visitor.cleanup') }}" method="POST">
    @csrf
    <button class="btn btn-sm btn-neutral" type="submit">Cleanup</button>
  </form>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.visitor.index')" routeAction="/admin/api/role" name="visitor"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
