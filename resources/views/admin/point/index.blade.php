<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.point.index') }}">Point</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Point</p>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.point.index')" routeAction="/admin/api/point" name="point"></x-table>
  </div>

  <x-slot:js>
    <script title="alpine.js"></script>
  </x-slot:js>
</x-layout.admin>
