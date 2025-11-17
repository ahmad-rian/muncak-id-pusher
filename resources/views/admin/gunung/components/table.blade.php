<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td width="10%">Image</td>
        <td tabindex="0" x-on:click="sortData('nama')">
          <div class="flex cursor-pointer select-none justify-between gap-2">
            <span class="line-clamp-1 shrink grow">
              Nama
            </span>
            <span x-show="order == 'nama'" :class="direction == 'desc' && 'rotate-180'">^</span>
            <span x-show="order != 'nama'">-</span>
          </div>
        </td>
        <td>Lokasi</td>
        <td>Wilayah</td>
        <td width="15%">Aksi</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>
            @if ($item->getImageUrl())
              <img class="size-10 rounded-sm object-cover object-center" src="{{ $item->getImageUrl() }}"
                alt="Cover Image" />
            @else
              -
            @endif
          </td>
          <td>{{ $item->nama }}</td>
          <td>{{ $item->lokasi ?? '-' }}</td>
          <td>
            @php
              $wilayah = '-';
              if ($item->kabupatenKota) {
                  $kabupatenKotaNama = $item->kabupatenKota->nama_lain ?? $item->kabupatenKota->nama;
                  $provinsiNama = $item->kabupatenKota->provinsi ? ($item->kabupatenKota->provinsi->nama_lain ?? $item->kabupatenKota->provinsi->nama) : '';
                  $wilayah = $provinsiNama ? "{$kabupatenKotaNama}, {$provinsiNama}" : $kabupatenKotaNama;
              } elseif ($item->negara) {
                  $wilayah = $item->negara->nama_lain ?? $item->negara->nama;
              }
            @endphp

            {{ $wilayah }}
          </td>
          <td class="">
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.gunung.edit', $item) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.gunung.show', $item) }}">
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
