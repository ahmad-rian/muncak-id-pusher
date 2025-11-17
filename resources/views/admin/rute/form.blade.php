<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.rute.index') }}">Rute</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->id }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Rute</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post" enctype="multipart/form-data">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

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
          <div class="md:col-span-2">
            <x-form.input name="nama" label="Nama Rute" :value="old('nama', $data->nama)" required />
          </div>

          <x-form.select :route="route('admin.api.gunung.select')" name="gunung_id" label="Gunung" :value="old('gunung_id', $data->gunung_id)" required />
          
          <x-form.select :route="route('admin.api.negara.select')" name="negara_id" label="Negara" :value="old('negara_id', $data->negara_id)" />

          <x-form.input name="lokasi" label="Lokasi" :value="old('lokasi', $data->lokasi)" placeholder="Contoh: Taman Nasional Gunung Rinjani" />
          
          <x-form.select :route="route('admin.api.desa.select')" name="kode_desa" label="Desa" :value="old('kode_desa', $data->kode_desa)" />

          <div class="md:col-span-2">
            <x-form.textarea name="deskripsi" label="Deskripsi" :value="old('deskripsi', $data->deskripsi)" />
          </div>

          <x-form.input name="segmentasi" label="Segmentasi" :value="old('segmentasi', $data->segmentasi)" required />

          <x-form.select-option name="rute_tingkat_kesulitan_id" label="Tingkat Kesulitan" :value="old('rute_tingkat_kesulitan_id', $data->rute_tingkat_kesulitan_id)"
            :option="$tingkatKesulitan" />

          <div class="md:col-span-2">
            <x-form.image name="image" label="Image Cover (jpg, jpeg, png, webp)" :value="$data->getImageUrl()" />
          </div>

          <div class="md:col-span-2">
            <x-form.text-editor name="informasi" label="Informasi" :value="old('informasi', $data->informasi)"></x-text-editor>
          </div>

          <div class="md:col-span-2">
            <x-form.text-editor name="aturan_dan_larangan" label="Aturan dan Larangan"
              :value="old('aturan_dan_larangan', $data->aturan_dan_larangan)"></x-text-editor>
          </div>

          <div class="md:col-span-2">
            <div class="label">
              <span class="label-tex font-medium" for="is_cuaca_siap">Kesiapan Rute</span>
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
                  <input class="toggle toggle-sm shrink-0 grow-0" id="is_kriteria_jalur_siap" value="1"
                    type="checkbox" name="is_kriteria_jalur_siap" @checked(old('is_kriteria_jalur_siap', $data->is_kriteria_jalur_siap)) />
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

    @if ($type == 'Create')
      <div class="card card-compact mt-6 rounded-lg border border-base-300 shadow-sm" x-data="{ show: true, isGalleryNew: false }">
        <div class="card-body gap-y-0">
          <div class="card-title flex cursor-pointer items-center gap-4" x-on:click="show = !show">
            <p class="shrink grow">Gallery</p>
            <div class="flex items-center justify-center">
              <button class="btn btn-square btn-ghost btn-xs" type="button">
                <span :class="show && 'rotate-180'">
                  <x-gmdi-arrow-drop-up-r class="size-6 transition-transform" />
                </span>
              </button>
            </div>
          </div>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="show" x-collapse x-cloak>
            <div class="col-span-2">
              <x-form.image-multiple name="gallery" label="Gallery (jpg, jpeg, png, webp)" :value="$data->getGalleryUrls()" />
            </div>
          </div>
        </div>
      </div>
    @endif

    @if ($type == 'Edit')
      <div class="card card-compact mt-6 rounded-lg border border-base-300 shadow-sm" x-data="{ show: true, isGalleryNew: false }">
        <div class="card-body gap-y-0">
          <div class="card-title flex cursor-pointer items-center gap-4" x-on:click="show = !show">
            <p class="shrink grow">Gallery</p>
            <div class="flex items-center justify-center">
              <button class="btn btn-square btn-ghost btn-xs" type="button">
                <span :class="show && 'rotate-180'">
                  <x-gmdi-arrow-drop-up-r class="size-6 transition-transform" />
                </span>
              </button>
            </div>
          </div>

          <div>
            <label class="label cursor-pointer justify-start gap-2">
              <span class="label-text">Override Gallery</span>
              <input class="toggle toggle-primary" type="checkbox" x-model="isGalleryNew" />
              <span class="label-text">Gallery Baru</span>
            </label>
          </div>

          <template x-if="!isGalleryNew">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="show" x-collapse x-cloak>
              <div class="col-span-2">
                <x-form.image-multiple name="gallery" label="Gallery (jpg, jpeg, png, webp)" :value="$data->getGalleryUrls()" />
              </div>
            </div>
          </template>

          <template x-if="isGalleryNew">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="show" x-collapse x-cloak>
              <div class="col-span-2">
                <x-form.image-multiple name="gallery_new" label="Gallery Baru (jpg, jpeg, png, webp)" />
              </div>
            </div>
          </template>
        </div>
      </div>
    @endif

    <div class="card card-compact mt-6 rounded-lg border border-base-300 shadow-sm" x-data="{ show: true }">
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
            <x-form.input name="a_k" label="Kalori A" :value="old('a_k', $data->a_k)" />
            <x-form.input name="b_k" label="Kalori B" :value="old('b_k', $data->b_k)" />
            <x-form.input name="c_k" label="Kalori C" :value="old('c_k', $data->c_k)" />
            <x-form.input name="d_k" label="Kalori D" :value="old('d_k', $data->d_k)" />
          </div>

          <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
            <x-form.input name="a_wt" label="Waktu Tempuh A" :value="old('a_wt', $data->a_wt)" />
            <x-form.input name="b_wt" label="Waktu Tempuh B" :value="old('b_wt', $data->b_wt)" />
            <x-form.input name="c_wt" label="Waktu Tempuh C" :value="old('c_wt', $data->c_wt)" />
            <x-form.input name="d_wt" label="Waktu Tempuh D (kg)" :value="old('d_wt', $data->d_wt)" />
            <x-form.input name="e_wt" label="Waktu Tempuh E" :value="old('e_wt', $data->e_wt)" />
            <x-form.input name="f_wt" label="Waktu Tempuh F" :value="old('f_wt', $data->f_wt)" />
            <x-form.input name="g_wt" label="Waktu Tempuh G" :value="old('g_wt', $data->g_wt)" />
            <x-form.input name="h_wt" label="Waktu Tempuh H" :value="old('h_wt', $data->h_wt)" />
            <x-form.input name="i_wt" label="Waktu Tempuh I" :value="old('i_wt', $data->i_wt)" />
            <x-form.input name="j_wt" label="Waktu Tempuh J" :value="old('j_wt', $data->j_wt)" />
            <x-form.input name="k_wt" label="Waktu Tempuh K" :value="old('k_wt', $data->k_wt)" />
          </div>

          <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
            <x-form.input name="a_cps" label="Cuaca per Segmen A" :value="old('a_cps', $data->a_cps)" />
            <x-form.input name="b_cps" label="Cuaca per Segmen B" :value="old('b_cps', $data->b_cps)" />
          </div>

          <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 md:grid-cols-4">
            <x-form.input name="c_kr" label="Kriteria Jalur C" :value="old('c_kr', $data->c_kr)" />
            <x-form.input name="d_kr" label="Kriteria Jalur D" :value="old('d_kr', $data->d_kr)" />
            <x-form.input name="e_kr" label="Kriteria Jalur E" :value="old('e_kr', $data->e_kr)" />
            <x-form.input name="f_kr" label="Kriteria Jalur F" :value="old('f_kr', $data->f_kr)" />
            <x-form.input name="g_kr" label="Kriteria Jalur G" :value="old('g_kr', $data->g_kr)" />
            <x-form.input name="h_kr" label="Kriteria Jalur G" :value="old('h_kr', $data->h_kr)" />
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" id="submitButton" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.rute.index') }}">Cancel</a>
    </div>

    @if ($errors->any())
      <div class="alert alert-error mt-4">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </form>

  <x-slot:js>
    <script title="alpine.js">
      // 
    </script>
  </x-slot:js>
</x-layout.admin>
