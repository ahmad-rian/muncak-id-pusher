<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td>User</td>
        <td>Rute</td>
        <td width="40%">Content</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>
            <div class="flex items-center gap-x-2 overflow-hidden">
              <img class="size-10 shrink-0 grow-0 rounded-sm object-cover object-center" src="{{ $item->user->avatar }}"
                alt="user-photo-profile" />
              <div class="shrink grow">
                <p class="line-clamp-1 font-medium">{{ $item->user->name }}</p>
                <p class="line-clamp-1 text-sm text-base-content/70">{{ "@{$item->user->username}" }}</p>
              </div>
            </div>
          </td>
          <td>{{ "Gunung {$item->rute->gunung->nama} via {$item->rute->nama}" }}</td>
          <td>
            {{ $item->content }}
          </td>
          <td>
            <div class="flex gap-1 align-middle">
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.comment.show', $item) }}">
                Lihat
              </a>
              <button class="btn btn-error btn-xs" x-on:click="deleteAction({{ $item->id }})">
                Hapus
              </button>
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
