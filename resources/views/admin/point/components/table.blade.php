<table class="table table-zebra" x-init="count = {{ $count }}">
  @if ($count)
    <thead>
      <tr>
        <td width="5%"></td>
        <td width="20%">Gallery</td>
        <td width="20%">Nama</td>
        <td>Waypoin & Cuaca</td>
        <td>Lat, Long, Elev</td>
      </tr>
    </thead>

    <tbody>
      @foreach ($data as $item)
        <tr class="hover">
          <td>{{ $loop->iteration }}</td>
          <td>
            <div class="no-scrollbar flex items-center gap-2 overflow-y-auto">
              @foreach ($item->getGalleryUrls() as $url)
                <img class="size-10 shrink-0 grow-0 rounded-sm object-cover object-center" src="{{ $url }}"
                  alt="Galleri" />
              @endforeach
            </div>
          </td>
          <td>{{ $item->nama ?? '-' }}</td>
          <td class="space-y-2">
            <label class="label justify-start gap-1 py-0" for="{{ "is_waypoint_{$item->id}" }}">
              <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_waypoint_{$item->id}" }}"
                data-name="is_waypoint" x-on:change="updateData({{ $item->id }})" value="1" type="checkbox"
                name="{{ "is_waypoint_{$item->id}" }}" @checked($item->is_waypoint) />
              <span class="label-text text-xs">Waypoint</span>
            </label>
            <label class="label justify-start gap-1 py-0" for="{{ "is_lokasi_prediksi_cuaca_{$item->id}" }}">
              <input class="toggle toggle-sm shrink-0 grow-0" id="{{ "is_lokasi_prediksi_cuaca_{$item->id}" }}"
                data-name="is_lokasi_prediksi_cuaca" x-on:change="updateData({{ $item->id }})" value="1"
                type="checkbox" name="{{ "is_lokasi_prediksi_cuaca_{$item->id}" }}" @checked($item->is_lokasi_prediksi_cuaca) />
              <span class="label-text text-xs">Lokasi Cuaca</span>
            </label>
          </td>
          @php
            $lat = number_format($item->lat, 6, '.', '');
            $long = number_format($item->long, 6, '.', '');
            $elev = number_format($item->elev, 2, '.', '');
          @endphp
          <td>{{ "{$lat}, {$long}, {$elev} m" }}</td>
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
