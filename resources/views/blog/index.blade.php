<x-layout.app>

  <x-slot:title>{{ 'Temukan Artikel Menarik' }}</x-slot:title>

  <div>
    <img
      class="h-60 w-full object-cover object-center md:h-72 lg:h-96"
      src="https://images.unsplash.com/photo-1661218271501-f81834ff14b0?q=80&w=3424&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
      alt="">
  </div>

  <main class="container mx-auto px-4 pb-12 pt-6 md:px-6 xl:px-8">
    <div class="mx-auto max-w-screen-sm text-center">
      <h1 class="font-merriweather text-2xl font-semibold">Artikel Kami</h1>
      <h2 class="mt-1 text-pretty text-lg text-base-content/70">
        Temukan artikel menarik tentang jalur pendakian gunung, tips mendaki, dan pengalaman pendaki lainnya di blog
        kami.
      </h2>
    </div>

    {{-- <div class="mt-4 flex items-center justify-center">
      <form class="flex shrink grow-0 basis-full gap-x-4 lg:basis-1/3" action="{{ route('blog.index') }}" method="get">
        <input type="hidden" name="page" value="{{ $page }}">
        <div class="join w-full rounded-md">
          <label class="input join-item input-bordered flex grow items-center gap-2">
            <input class="grow" type="text" name="q" placeholder="Cari artikel"
              value="{{ $q }}" />
          </label>
          <button class="btn join-item btn-neutral cursor-pointer border" type="submit">
            <x-gmdi-search-r class="size-6 opacity-70" />
          </button>
        </div>
      </form>
    </div> --}}

    @if (!count($blogs))
      <div class="mt-6 text-center">
        <x-gmdi-article-r class="mx-auto size-16 opacity-70" />
        <p class="text-lg text-base-content/70">Tidak ada artikel yang ditemukan.</p>
      </div>
    @else
      <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($blogs as $item)
          <div>
            <a class="card card-compact rounded-lg border border-base-300 bg-base-100 shadow-md transition-colors hover:bg-base-200"
              href="{{ route('blog.slug', [$item->slug]) }}" title="{{ $item->title }}">
              <figure>
                <img class="h-48 w-full object-cover" src="{{ $item->getImageUrl() }}" alt="{{ $item->title }}">
              </figure>
              <div class="card-body">
                <div>
                  <p class="line-clamp-2 text-pretty font-merriweather text-lg font-semibold">{{ $item->title }}
                  </p>
                  <p class="mt-1 line-clamp-2 text-base-content/70">{{ $item->deskripsi_singkat }}</p>
                  <p class="mt-2 text-end text-xs text-base-content/70">{{ $item->created_at_human }}</p>
                </div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    @endif

    @if (count($pagination) > 1)
      <div class="mt-4 flex justify-end">
        <div class="join">
          @foreach ($pagination as $item)
            @if ($item == 'prev')
              <a class="btn join-item btn-sm" href="{{ route('index', ['page' => $page - 1]) }}">
                «
              </a>
            @elseif ($item == 'next')
              <a class="btn join-item btn-sm" href="{{ route('index', ['page' => $page + 1]) }}">
                »
              </a>
            @elseif (is_numeric($item))
              <a class="{{ $page == $item ? 'btn-active btn-primary' : null }} btn join-item btn-sm"
                href="{{ route('index', ['page' => $item]) }}">{{ $item }}</a>
            @else
              <span class="btn btn-disabled join-item btn-sm hidden sm:inline-flex">...</span>
            @endif
          @endforeach
        </div>
      </div>
    @endif
  </main>

  <x-slot:head>
    <meta name="description"
      content="Temukan artikel menarik tentang jalur pendakian gunung, tips mendaki, dan pengalaman pendaki lainnya di blog kami.">
    <meta name="keywords"
      content="blog pendakian, artikel pendakian, tips mendaki, pengalaman pendaki, jalur pendakian, muncak.id">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url()->current() }}" hreflang="id" />
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta property="og:type" content="website">
    <meta property="og:title" content="Artikel - muncak.id">
    <meta property="og:description"
      content="Temukan artikel menarik tentang jalur pendakian gunung, tips mendaki, dan pengalaman pendaki lainnya di blog kami.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="muncak.id">
    {{-- {!! $schemaOrg ?? '' !!} --}}
  </x-slot:head>
</x-layout.app>
