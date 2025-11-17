<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.comment.index') }}">Comment</a></li>
      <li>List</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Comment</p>
  </div>

  <div class="mt-6 rounded-lg border border-base-300">
    <x-table :routeList="route('admin.api.comment.index')" routeAction="/admin/api/comment" name="comment"></x-table>
  </div>

  <x-slot:js></x-slot:js>
</x-layout.admin>
