<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.comment.index') }}">Comment</a></li>
      <li>Show</li>
      <li>{{ $data->id }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Comment</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th width="15%">ID</th>
          <td>{{ $data->id }}</td>
        </tr>
        <tr>
          <th>Avatar</th>
          <td>
            <img class="h-16 rounded-sm object-contain object-center" src="{{ $data->user->avatar }}"
              alt="user-photo-profile">
          </td>
        </tr>
        <tr>
          <th>User</th>
          <td>{{ $data->user->name }}</td>
        </tr>
        <tr>
          <th>Username</th>
          <td>{{ $data->user->username }}</td>
        </tr>
        <tr>
          <th>Gallery</th>
          <td>
            <div class="flex flex-wrap gap-4">
              @foreach ($data->gallery as $item)
                <img class="size-24 rounded-sm object-contain object-center" src="{{ $item }}"
                  alt="comment-gallery">
              @endforeach
            </div>
          </td>
        </tr>
        <tr>
          <th>Rute</th>
          <td>{{ "Gunung {$data->rute->gunung->nama} via {$data->rute->nama}" }}</td>
        </tr>
        <tr>
          <th>Content</th>
          <td>{{ $data->content }}</td>
        </tr>
        <tr>
          <th>Rating</th>
          <td>{{ $data->rating }}</td>
        </tr>
        <tr>
          <th>Updated At</th>
          <td>{{ $data->created_at }}</td>
        </tr>
        <tr>
          <th>Created At</th>
          <td>{{ $data->updated_at }}</td>
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
