<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.gunung.index') }}">Gunung</a></li>
      <li>Show</li>
      <li>{{ $data->id }}</li>
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">Show Gunung</p>
  </div>

  <div class="mt-6 overflow-x-auto rounded-lg border border-base-300">
    <div class="min-w-[30rem]">
      <table class="table table-zebra">
        <tr>
          <th>Image</th>
          <td>
            @if ($data->getImageUrl())
              <img class="h-32 rounded-sm object-contain object-center" src="{{ $data->getImageUrl() }}" alt="Image">
            @else
              -
            @endif
          </td>
        </tr>
        <tr>
          <th width="15%">ID</th>
          <td>{{ $data->id }}</td>
        </tr>
        <tr>
          <th>Nama</th>
          <td>{{ $data->nama }}</td>
        </tr>
        <tr>
          <th>Slug</th>
          <td>{{ $data->slug }}</td>
        </tr>
        <tr>
          <th>Lokasi</th>
          <td>{{ $data->lokasi ?? '-' }}</td>
        </tr>
        <tr>
          <th>Negara</th>
          <td>{{ $data->negara ? ($data->negara->nama_lain ?? $data->negara->nama) : '-' }}</td>
        </tr>
        <tr>
          <th>Deskripsi</th>
          <td>{{ $data->deskripsi }}</td>
        </tr>
        <tr>
          <th>Latitude</th>
          <td>{{ $data->lat }}</td>
        </tr>
        <tr>
          <th>Longitude</th>
          <td>{{ $data->long }}</td>
        </tr>
        <tr>
          <th>Elevasi</th>
          <td>{{ $data->elev }}</td>
        </tr>
        <tr>
          <th>Wilayah</th>
          @php
            $wilayah = '-';
            if ($data->kode_kabupaten_kota && $data->kabupatenKota) {
                $kabupatenNama = $data->kabupatenKota->nama_lain ?? $data->kabupatenKota->nama;
                $provinsiNama = $data->kabupatenKota->provinsi ? ($data->kabupatenKota->provinsi->nama_lain ?? $data->kabupatenKota->provinsi->nama) : '';
                $wilayah = $provinsiNama ? "{$kabupatenNama}, {$provinsiNama}" : $kabupatenNama;
            } elseif ($data->negara) {
                $wilayah = $data->negara->nama_lain ?? $data->negara->nama;
            }
          @endphp
          <td>{{ $wilayah }}</td>
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
