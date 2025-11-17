<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td>Visitor</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td class="space-y-1">
            <p class="text-sm text-base-content/70">{{ $item->path }}</p>
            <p class="font-semibold">{{ $item->ip_address }}</p>
            <p>{{ $item->user_agent }}</p>
            <p class="text-sm text-base-content/70">Referrer: {{ $item->referrer }}</p>
            <p class="text-xs text-base-content/70">Dibuat {{ $item->created_at_human }}</p>
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
