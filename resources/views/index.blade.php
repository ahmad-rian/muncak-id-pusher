<x-layout.app>

  <x-slot:title>Rencanakan Destinasi Pendakianmu</x-slot:title>

  <header class="relative flex h-96 flex-col justify-center bg-cover bg-center pb-5"
    style="background-image: url('https://images.unsplash.com/photo-1604143055124-a0130cc54faf?q=65&w=1600&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D')">
    <div class="container relative mx-auto max-w-screen-lg bg-cover bg-center px-4 pt-20 md:px-6 lg:px-8 xl:px-12">
      <div class="mx-auto mt-2 rounded-lg p-4 text-center">
        <h1 class="px-4 font-merriweather text-4xl font-extrabold text-white md:text-5xl">MUNCAK.ID</h1>
        <h2 class="mt-3 text-lg text-gray-200">
          Menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan dalam merencanakan pendakian
          gunung dan penjelajahan pegunungan di Indonesia dan luar negeri
        </h2>
      </div>
    </div>

    <div class="absolute inset-x-4 bottom-0 z-[10] mx-auto hidden max-w-xl translate-y-1/2">
      <form action="" method="get">
        <div class="join w-full rounded-full shadow-md">
          <label class="input join-item input-bordered input-primary btn-md flex grow items-center gap-2 md:input-lg">
            <input class="grow md:text-lg" type="text" name="s" placeholder="Cari destinasi pendakianmu" />
          </label>
          <button class="btn btn-primary join-item btn-md cursor-pointer border md:btn-lg" type="submit">
            <gmdi-search-r class="size-6 opacity-70" />
          </button>
        </div>
      </form>
    </div>
  </header>

  <x-slot:head>
    <!-- Preconnect to Unsplash for faster image loading -->
    <link rel="preconnect" href="https://images.unsplash.com">
    <link rel="dns-prefetch" href="https://images.unsplash.com">

    <!-- Preload hero image (LCP element) -->
    <link rel="preload" as="image"
      href="https://images.unsplash.com/photo-1604143055124-a0130cc54faf?q=65&w=1600&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
      fetchpriority="high">
  </x-slot:head>

  <section class="container relative mx-auto px-4 py-12 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
    <div>
      <p class="text-center font-merriweather text-2xl font-semibold">Jalur Pendakian</p>
      <p class="mt-1 text-center text-base-content/70">Temukan jalur pendakian yang sesuai dengan kebutuhanmu</p>

      <div class="mt-4 flex items-center justify-center">
        <form class="flex shrink grow gap-x-4 md:grow-0 md:basis-2/3" action="{{ route('index') }}" method="get"
          x-ref="form" x-data="{
              resetPageOnCountryChange() {
                  this.$refs.pageInput.value = '1';
                  this.$refs.form.submit();
              }
          }">
          <input type="hidden" name="page" value="{{ $page }}" x-ref="pageInput">
          <div class="flex w-full flex-col gap-2 md:flex-row">
            <select class="select select-bordered w-full grow md:w-48 md:grow-0" name="negara"
              x-on:change="resetPageOnCountryChange()">
              @foreach ($negaraList as $country)
                <option value="{{ $country->slug }}" {{ $negara == $country->slug ? 'selected' : '' }}>
                  {{ $country->nama_lain ?? $country->nama }}
                </option>
              @endforeach
            </select>
            <div class="join w-full grow md:w-auto">
              <label class="input join-item input-bordered flex grow items-center gap-2">
                <input class="grow" type="text" name="q" placeholder="Cari destinasi pendakianmu" value="{{ $q }}" />
              </label>
              <button class="btn join-item btn-neutral cursor-pointer border" type="submit">
                <x-gmdi-search-r class="size-6 opacity-70" />
              </button>
            </div>
          </div>
        </form>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($rute as $item)
          <a class="group card-compact block" href="{{ $item->path }}">
            <figure>
              <img class="h-48 w-full rounded-box object-cover object-center" src="{{ $item->image }}"
                alt="{{ 'jalur-pendakian' }}" loading="{{ $loop->index < 4 ? 'eager' : 'lazy' }}" width="400"
                height="192" />
            </figure>
            <div class="card-body !px-0">
              <div>
                <div class="line-clamp-1 font-merriweather text-lg font-bold group-hover:underline">
                  {{ $item->nama }}
                </div>
                <p class="line-clamp-1 text-base-content/70">{{ $item->lokasi }}</p>
                <div
                  class="mt-1 line-clamp-1 flex h-[calc(1*1.25rem)] flex-wrap items-center gap-2 overflow-hidden text-sm text-base-content/90">
                  @if ($item->tingkat_kesulitan)
                    <span>{{ $item->tingkat_kesulitan }}</span>
                    <span class="size-1 rounded-full bg-base-content/70"></span>
                  @endif
                  @if ($item->comment_count)
                    <span class="flex items-center gap-0.5">
                      <x-gmdi-star-r class="size-3 text-yellow-500" />
                      <span>{{ $item->comment_rating }}</span>
                      <span>({{ $item->comment_count }})</span>
                    </span>
                    <span class="size-1 rounded-full bg-base-content/70"></span>
                  @endif
                  @if ($item->jarak_total)
                    <span>{{ $item->jarak_total }}</span>
                    <span class="size-1 rounded-full bg-base-content/70"></span>
                  @endif
                  @if ($item->waktu_tempuh)
                    <span>{{ $item->waktu_tempuh }}</span>
                  @endif
                </div>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      @if (count($pagination) > 1)
        <div class="mt-4 flex justify-end">
          <div class="join">
            @foreach ($pagination as $item)
              @if ($item == 'prev')
                <a class="btn join-item btn-sm"
                  href="{{ route('index', ['page' => $page - 1, 'negara' => $negara, 'q' => $q]) }}">
                  «
                </a>
              @elseif ($item == 'next')
                <a class="btn join-item btn-sm"
                  href="{{ route('index', ['page' => $page + 1, 'negara' => $negara, 'q' => $q]) }}">
                  »
                </a>
              @elseif (is_numeric($item))
                <a class="{{ $page == $item ? 'btn-active btn-primary' : null }} btn join-item btn-sm"
                  href="{{ route('index', ['page' => $item, 'negara' => $negara, 'q' => $q]) }}">{{ $item }}</a>
              @else
                <span class="btn btn-disabled join-item btn-sm hidden sm:inline-flex">...</span>
              @endif
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  <section>
    <div class="container relative mx-auto px-4 py-12 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
      <div class="grid grid-cols-1 items-center gap-x-12 gap-y-6 md:grid-cols-2">
        <div class="grid grid-cols-2 gap-4">
          <img class="row-span-2 h-[21rem] w-full rounded-md object-cover object-center"
            src="https://images.unsplash.com/photo-1633512424789-9ad3655bec31?q=80&w=3264&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="tentang-1" loading="lazy" width="800" height="672">
          <img class="h-40 w-full rounded-md object-cover object-center"
            src="https://images.unsplash.com/photo-1671965448417-0582cb361168?q=80&w=3270&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="tentang-2" loading="lazy" width="400" height="160">
          <img class="h-40 w-full rounded-md object-cover object-center"
            src="https://images.unsplash.com/photo-1476158085676-e67f57ed9ed7?q=80&w=2800&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
            alt="tentang-3" loading="lazy" width="400" height="160">
        </div>
        <div>
          <p class="font-merriweather text-2xl font-semibold lg:text-3xl">Tentang</p>
          <p class="mt-6 text-balance indent-8">
            <span class="font-merriweather font-semibold">muncak.id</span> merupakan aplikasi yang dirancang untuk
            membantu pendaki dengan memberikan panduan mendaki gunung maupun pegunungan yang komprehensif dan
            terstruktur, sehingga setiap langkah perjalanan dapat direncanakan dengan cermat.
          </p>
          <p class="mt-2 text-balance indent-8">
            Selain itu, aplikasi ini menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan
            dalam merencanakan pendakian
            gunung dan penjelajahan pegunungan di Indonesia dan luar negeri. Dengan akses ke berbagai informasi penting,
            pengguna dapat
            lebih siap dan percaya diri dalam menghadapi tantangan alam.
          </p>
          <div class="mt-3">
            <div class="flex items-center gap-x-2 text-balance">
              <x-gmdi-pin-drop-r class="size-5 shrink-0 grow-0" />
              <p>
                Pusat Pengembangan Teknologi Petualangan Tropis. Laboratorium Teknik Industri, Teknik Geologi dan
                Informatika Universitas Jenderal
                Soedirman
              </p>
            </div>
            <div class="mt-3 flex items-center gap-x-2">
              <x-gmdi-mail-r class="size-5 shrink-0 grow-0" />
              <a class="hover:underline"
                href="mailto:laboratoriumindustri@unsoed.ac.id">laboratoriumindustri@unsoed.ac.id</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="container relative mx-auto max-w-screen-xl px-4 py-12 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
    <div class="text-center">
      <p class="font-merriweather text-2xl font-semibold lg:text-3xl">Fitur Unggulan</p>
      <div class="mt-8 grid grid-cols-2 gap-6 lg:grid-cols-4">
        <div>
          <div
            class="mx-auto flex size-20 items-center justify-center rounded-full border border-base-300 bg-primary text-white">
            <x-gmdi-phone-android-r class="size-12" />
          </div>
          <div class="mt-4">
            <p class="font-merriweather text-lg font-medium">Kelengkapan Informasi</p>
            <p class="mt-2 text-pretty text-sm text-base-content/70">
              Menyajikan informasi detail yang lengkap dan terpercaya untuk setiap rute pendakian
            </p>
          </div>
        </div>
        <div>
          <div
            class="mx-auto flex size-20 items-center justify-center rounded-full border border-base-300 bg-success text-white">
            <x-gmdi-supervisor-account-r class="size-12" />
          </div>
          <div class="mt-4">
            <p class="font-merriweather text-lg font-medium">Ulasan Para Pendaki</p>
            <p class="mt-2 text-pretty text-sm text-base-content/70">
              Baca pengalaman dan penilaian dari pendaki lain untuk panduan yang lebih baik
            </p>
          </div>
        </div>
        <div>
          <div
            class="mx-auto flex size-20 items-center justify-center rounded-full border border-base-300 bg-error text-white">
            <x-gmdi-cloud-sync-r class="size-12" />
          </div>
          <div class="mt-4">
            <p class="font-merriweather text-lg font-medium">Prediksi Cuaca dan Kalori</p>
            <p class="mt-2 text-pretty text-sm text-base-content/70">
              Lihat prakiraan cuaca serta estimasi kalori yang terbakar selama pendakian
            </p>
          </div>
        </div>
        <div>
          <div
            class="mx-auto flex size-20 items-center justify-center rounded-full border border-base-300 bg-secondary text-white">
            <x-gmdi-map-r class="size-12" />
          </div>
          <div class="mt-4">
            <p class="font-merriweather text-lg font-medium">Peta dan Waypoints</p>
            <p class="mt-2 text-pretty text-sm text-base-content/70">
              Jelajahi peta dan titik-titik penting untuk navigasi yang lebih mudah
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <x-slot:head>
    <meta name="description"
      content="muncak.id menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan dalam merencanakan pendakian gunung dan penjelajahan pegunungan di Indonesia dan luar negeri.">
    <meta name="keywords"
      content="jalur pendakian, jalur pendakian gunung, peta jalur pendakian, jelajahi jalur pendakian, rute pendakian gunung, peta interaktif, rute pendakian">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url('/') }}" hreflang="id" />
    <link rel="canonical" href="{{ url('/') }}" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="muncak.id - Rencanakan Destinasi Pendakianmu">
    <meta property="og:description"
      content="muncak.id menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan dalam merencanakan pendakian gunung dan penjelajahan pegunungan di Indonesia dan luar negeri.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="muncak.id">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>

</x-layout.app>