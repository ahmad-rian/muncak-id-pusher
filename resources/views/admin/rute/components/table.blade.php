<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td width="5%">Image</td>
        <td tabindex="0" x-on:click="sortData('nama')">
          <div class="flex cursor-pointer select-none justify-between gap-2">
            <span class="line-clamp-1 shrink grow">
              Nama
            </span>
            <span x-show="order == 'nama'" :class="direction == 'desc' && 'rotate-180'">^</span>
            <span x-show="order != 'nama'">-</span>
          </div>
        </td>
        <td>Gunung</td>
        <td>Lokasi</td>
        <td>Wilayah</td>
        <td>Kesiapan</td>
        <td width="10%">Aksi</td>
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
          <td>
            <div class="flex items-center gap-1">
              {{ $item->nama }}
              @if ($item->is_verified)
                <x-gmdi-verified-r class="size-4 text-info" />
              @endif
            </div>
          </td>
          <td>{{ $item->gunung->nama }}</td>
          <td>{{ $item->lokasi ?? '-' }}</td>
          <td>
            @php
              $wilayah = '-';
              if ($item->desa) {
                  $desaNama = $item->desa->nama_lain ?? $item->desa->nama;
                  $kecamatanNama = $item->desa->kecamatan->nama_lain ?? $item->desa->kecamatan->nama;
                  $kabupatenNama = $item->desa->kecamatan->kabupatenKota->nama_lain ?? $item->desa->kecamatan->kabupatenKota->nama;
                  $provinsiNama = $item->desa->kecamatan->kabupatenKota->provinsi->nama_lain ?? $item->desa->kecamatan->kabupatenKota->provinsi->nama;
                  $wilayah = "{$desaNama}, {$kecamatanNama}, {$kabupatenNama}, {$provinsiNama}";
              } elseif ($item->negara) {
                  $wilayah = $item->negara->nama_lain ?? $item->negara->nama;
              }
            @endphp

            {{ $wilayah }}
          </td>
          <td>
            <div class="space-y-2">
              <label class="label justify-start gap-1 py-0" for="{{ "is_cuaca_siap_{$item->id}" }}">
                <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_cuaca_siap_{$item->id}" }}"
                  data-name="is_cuaca_siap" x-on:change="updateData({{ $item->id }})" value="1" type="checkbox"
                  name="{{ "is_cuaca_siap_{$item->id}" }}" @checked($item->is_cuaca_siap) />
                <span class="label-text text-xs">Cuaca</span>
              </label>
              <label class="label justify-start gap-1 py-0" for="{{ "is_kalori_siap_{$item->id}" }}">
                <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_kalori_siap_{$item->id}" }}"
                  data-name="is_kalori_siap" x-on:change="updateData({{ $item->id }})" value="1"
                  type="checkbox" name="{{ "is_kalori_siap_{$item->id}" }}" @checked($item->is_kalori_siap) />
                <span class="label-text text-xs">Kalori</span>
              </label>
              <label class="label justify-start gap-1 py-0" for="{{ "is_kriteria_jalur_siap_{$item->id}" }}">
                <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_kriteria_jalur_siap_{$item->id}" }}"
                  data-name="is_kriteria_jalur_siap" x-on:change="updateData({{ $item->id }})" value="1"
                  type="checkbox" name="{{ "is_kriteria_jalur_siap_{$item->id}" }}" @checked($item->is_kriteria_jalur_siap) />
                <span class="label-text text-xs">Kriteria Jalur</span>
              </label>
            </div>
          </td>
          <td class="">
            <div class="flex gap-1 align-middle">
              <a class="btn btn-success btn-xs" href="{{ route('admin.rute.edit', $item->id) }}">
                Edit
              </a>
              <a class="btn btn-secondary btn-xs" href="{{ route('admin.rute.show', $item->id) }}">
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
