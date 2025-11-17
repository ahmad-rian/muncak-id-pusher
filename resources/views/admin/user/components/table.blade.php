<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td width="5%">Avatar</td>
        <td>Email</td>
        <td>Name</td>
        <td>Username</td>
        <td>Role</td>
        <td>Provider</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>
            @if ($item->getAvatarUrl())
              <img class="size-10 rounded-sm object-cover object-center" src="{{ $item->getAvatarUrl() }}"
                alt="Cover Image" />
            @else
              -
            @endif
          </td>
          <td>{{ $item->name }}</td>
          <td>{{ $item->email }}</td>
          <td>{{ $item->username }}</td>
          <td>
            @if ($item->isAdmin)
              <div class="badge badge-success badge-sm uppercase">ADMIN</div>
            @else
              <div class="badge badge-sm uppercase">USER</div>
            @endif
          </td>
          <td>
            @if (count($item->userProvider))
              @foreach ($item->userProvider as $provider)
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
          <td>
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.user.edit', $item) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.user.show', $item) }}">
                Lihat
              </a>
              @if (auth()->user()->id !== $item->id)
                <button class="btn btn-error btn-xs" x-on:click="deleteAction('{{ $item->id }}')">
                  Hapus
                </button>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  @else
    <tr>
      <td>
        <div class="flex flex-col items-center py-8 text-base-content/70">
          <x-gmdi-folder-off-r class="size-16" />
          <p>No Data Available</p>
        </div>
      </td>
    </tr>
  @endif
</table>
