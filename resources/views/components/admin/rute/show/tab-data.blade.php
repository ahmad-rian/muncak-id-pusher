@props(['data'])

<div class="card card-compact rounded-lg border border-base-300 shadow-sm" x-data="{ show: true }">
  <div class="card-body">
    <div class="card-title flex cursor-pointer items-center gap-4" x-on:click="show = !show">
      <p class="shrink grow">Informasi Rute</p>
      <div class="flex items-center justify-center">
        <button class="btn btn-square btn-ghost btn-xs" type="button">
          <span :class="show && 'rotate-180'">
            <x-gmdi-arrow-drop-up-r class="size-6 transition-transform" />
          </span>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="show" x-collapse x-cloak>
      <x-form.input name="nama" label="Nama Rute" :value="old('nama', $data->nama)" required readonly />
      <x-form.input name="slug" label="Slug" :value="old('nama', $data->slug)" required readonly />
      <x-form.select :route="route('admin.api.gunung.select')" name="gunung_id" label="Gunung" :value="old('gunung_id', $data->gunung_id)" readonly />
      <x-form.select :route="route('admin.api.negara.select')" name="negara_id" label="Negara" :value="old('negara_id', $data->negara_id)" readonly />
      <x-form.input name="lokasi" label="Lokasi" :value="old('lokasi', $data->lokasi)" readonly />
      <x-form.select :route="route('admin.api.desa.select')" name="kode_desa" label="Desa" :value="old('kode_desa', $data->kode_desa)" readonly />

      <div class="md:col-span-2">
        <x-form.textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)" required readonly />
      </div>

      <x-form.input type="number" name="segmentasi" label="Segmentasi" :value="old('segmentasi', $data->segmentasi)" required />
      <x-form.select :route="route('admin.api.rute-tingkat-kesulitan.select')" name="rute_tingkat_kesulitan_id" label="Tingkat Kesulitan" :value="old('rute_tingkat_kesulitan_id', $data->rute_tingkat_kesulitan_id)"
        readonly />

      <div class="md:col-span-2">
        <x-form.image name="image" label="Image Cover (jpg, jpeg, png, webp)" :value="$data->getImageUrl() ? $data->getImageUrl() : ''" readonly />
      </div>

      <div class="md:col-span-2">
        <div class="label">
          <span class="label-text font-medium" for="is_cuaca_siap">Kesiapan Rute</span>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div class="flex flex-col gap-x-4 gap-y-2 sm:flex-row sm:items-center md:col-span-2">
            <div class="flex items-center gap-1">
              <input class="toggle toggle-sm shrink-0 grow-0" id="is_cuaca_siap" value="1" type="checkbox"
                name="is_cuaca_siap" @checked(old('is_cuaca_siap', $data->is_cuaca_siap)) />
              <div class="label">
                <label class="label-text" for="is_cuaca_siap">Cuaca Siap</label>
              </div>
            </div>

            <div class="flex items-center gap-1">
              <input class="toggle toggle-sm shrink-0 grow-0" id="is_kalori_siap" value="1" type="checkbox"
                name="is_kalori_siap" @checked(old('is_kalori_siap', $data->is_kalori_siap)) />
              <div class="label">
                <label class="label-text" for="is_kalori_siap">Kalori Siap</label>
              </div>
            </div>

            <div class="flex items-center gap-1">
              <input class="toggle toggle-sm shrink-0 grow-0" id="is_kriteria_jalur_siap" value="1" type="checkbox"
                name="is_kriteria_jalur_siap" @checked(old('is_kriteria_jalur_siap', $data->is_kriteria_jalur_siap)) />
              <div class="label">
                <label class="label-text" for="is_kriteria_jalur_siap">Kriteria Jalur Siap</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <input class="checkbox checkbox-sm shrink-0 grow-0" id="is_verified" value="1" type="checkbox"
          name="is_verified" @checked(old('is_verified', $data->is_verified)) />
        <div class="label">
          <label class="label-text" for="is_verified">Verified</label>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card-compact card mt-6 rounded-lg border border-base-300 shadow-sm" x-data="{ show: true }">
  <div class="card-body">
    <div class="card-title flex cursor-pointer items-center gap-4" x-on:click="show = !show">
      <p class="shrink grow">Konstanta</p>
      <div class="flex items-center justify-center">
        <button class="btn btn-square btn-ghost btn-xs" type="button">
          <span :class="show && 'rotate-180'">
            <x-gmdi-arrow-drop-up-r class="size-6 transition-transform" />
          </span>
        </button>
      </div>
    </div>

    <div x-show="show" x-collapse x-cloak>
      <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-form.input name="a_k" label="Kalori A" :value="old('a_k', $data->a_k)" readonly />
        <x-form.input name="b_k" label="Kalori B" :value="old('b_k', $data->b_k)" readonly />
        <x-form.input name="c_k" label="Kalori C" :value="old('c_k', $data->c_k)" readonly />
        <x-form.input name="d_k" label="Kalori D" :value="old('d_k', $data->d_k)" readonly />
      </div>

      <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
        <x-form.input name="a_wt" label="Waktu Tempuh A" :value="old('a_wt', $data->a_wt)" readonly />
        <x-form.input name="b_wt" label="Waktu Tempuh B" :value="old('b_wt', $data->b_wt)" readonly />
        <x-form.input name="c_wt" label="Waktu Tempuh C" :value="old('c_wt', $data->c_wt)" readonly />
        <x-form.input name="d_wt" label="Waktu Tempuh D (kg)" :value="old('d_wt', $data->d_wt)" readonly />
        <x-form.input name="e_wt" label="Waktu Tempuh E" :value="old('e_wt', $data->e_wt)" readonly />
        <x-form.input name="f_wt" label="Waktu Tempuh F" :value="old('f_wt', $data->f_wt)" readonly />
      </div>

      <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
        <x-form.input name="a_cps" label="Cuaca per Segmen A" :value="old('a_cps', $data->a_cps)" readonly />
        <x-form.input name="b_cps" label="Cuaca per Segmen B" :value="old('b_cps', $data->b_cps)" readonly />
      </div>

      <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
        <x-form.input name="c_kr" label="Kriteria Jalur C" :value="old('c_kr', $data->c_kr)" readonly />
        <x-form.input name="d_kr" label="Kriteria Jalur D" :value="old('d_kr', $data->d_kr)" readonly />
        <x-form.input name="e_kr" label="Kriteria Jalur E" :value="old('e_kr', $data->e_kr)" readonly />
        <x-form.input name="f_kr" label="Kriteria Jalur F" :value="old('f_kr', $data->f_kr)" readonly />
        <x-form.input name="g_kr" label="Kriteria Jalur G" :value="old('g_kr', $data->g_kr)" readonly />
        <x-form.input name="h_kr" label="Kriteria Jalur G" :value="old('h_kr', $data->h_kr)" readonly />
      </div>
    </div>
  </div>
</div>
