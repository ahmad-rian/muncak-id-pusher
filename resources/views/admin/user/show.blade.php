<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.role.index') }}">Role</a></li>
      <li>Show</li>
      <li>{{ $data->kode }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Role</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th width="15%">ID</th>
          <td>{{ $data->id }}</td>
        </tr>
        <tr>
          <th>Nama</th>
          <td>{{ $data->name }}</td>
        </tr>
        <tr>
          <th>Email</th>
          <td>{{ $data->email }}</td>
        </tr>
        <tr>
          <th>Username</th>
          <td>{{ $data->username }}</td>
        </tr>
        <tr>
          <th>Role</th>
          <td>
            @if ($data->isAdmin)
              <div class="badge badge-success badge-sm uppercase">ADMIN</div>
            @else
              <div class="badge badge-sm uppercase">USER</div>
            @endif
          </td>
        </tr>
        <tr>
          <th>Provider</th>
          <td>
            @if (count($data->userProvider))
              @foreach ($data->userProvider as $provider)
                @if ($provider->provider == 'google')
                  <div class="badge badge-primary badge-sm uppercase">{{ $provider->provider }}</div>
                @elseif ($provider->provider == 'facebook')
                  <div class="badge badge-secondary badge-sm uppercase">{{ $provider->provider }}</div>
                @endif
              @endforeach
            @else
              <div class="badge badge-neutral badge-sm uppercase">EMAIL</div>
            @endif
          </td>
        </tr>
        <tr>
          <th>Email Verified At</th>
          <td>{{ $data->email_verified_at }}</td>
        </tr>
        <tr>
          <th>Updated At</th>
          <td>{{ $data->created_at }}</td>
        </tr>
        <tr>
          <th>Created At</th>
          <td>{{ $data->updated_at }}</td>
        </tr>
        <tr>
          <th>Avatar</th>
          <td>
            @if ($data->getAvatarUrl())
              <img class="h-32 rounded-sm object-contain object-center" src="{{ $data->getAvatarUrl() }}"
                alt="Image">
            @else
              -
            @endif
          </td>
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
