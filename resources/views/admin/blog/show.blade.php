<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.blog.index') }}">Blog</a></li>
      <li>Show</li>
      <li>{{ $data->id }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Blog</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th width="15%">ID</th>
          <td>{{ $data->id }}</td>
        </tr>
        <tr>
          <th>Judul</th>
          <td>{{ $data->title }}</td>
        </tr>
        <tr>
          <td>Author</td>
          <td>{{ "{$data->user->name} @{$data->user->username}" }}</td>
        </tr>
        <tr>
          <th>Image</th>
          <td>
            <img class="h-32 rounded-sm object-contain object-center" src="{{ $data->getImageUrl() }}" alt="Image">
          </td>
        </tr>
        <tr>
          <th>Deskripsi Singkat</th>
          <td>{{ $data->deskripsi_singkat }}</td>
        </tr>
        <tr>
          <th colspan="2">Konten</th>
        </tr>
        <tr>
          <td colspan="2">
            <div class="prose prose-sm dark:prose-invert">
              {!! $data->content !!}
            </div>
          </td>
        </tr>
        <tr>
          <th>Slug</th>
          <td>{{ $data->slug }}</td>
        </tr>
        <tr>
          <th>Published</th>
          <td>{{ $data->is_published ? 'Yes' : 'No' }}</td>
        </tr>
        <tr>
          <th>Updated At</th>
          <td>{{ $data->updated_at }}</td>
        </tr>
        <tr>
          <th>Created At</th>
          <td>{{ $data->created_at }}</td>
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
