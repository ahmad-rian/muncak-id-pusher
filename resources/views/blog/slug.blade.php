<x-layout.app>

  <x-slot:title>{{ $blog->title }}</x-slot:title>

  <main class="container mx-auto min-h-dvh max-w-screen-lg px-4 pb-12 pt-20 md:px-6 xl:px-8">
    @if ($blog->getImageUrl())
      <img class="h-60 w-full rounded-xl object-cover object-center md:h-72 lg:h-80 xl:h-96"
        src="{{ $blog->getImageUrl() }}" alt="{{ $blog->title }}">
    @endif

    <p class="mt-4 text-sm text-base-content/70">{{ $blog->created_at_human }}</p>
    <h1 class="mt-4 font-merriweather text-xl font-bold md:text-2xl">{{ $blog->title }}</h1>

    <div class="mt-6 flex items-center gap-2">
      <img class="size-10 shrink-0 grow-0 rounded-full object-cover object-center md:size-12"
        src="{{ $blog->user->getAvatarUrl() }}" alt="{{ $blog->user->name }}">
      <div class="shrink grow">
        <p class="font-medium">{{ $blog->user->name }}</p>
        <p class="text-base-content/70">{{ "@{$blog->user->username}" }}</p>
      </div>
    </div>

    <h2 class="mt-6 border-y border-base-content/10 py-4 text-lg text-base-content/70">{{ $blog->deskripsi_singkat }}
    </h2>

    <div class="prose mt-6 !w-full max-w-screen-lg dark:prose-invert md:prose-xl">
      {!! $blog->content !!}
    </div>
  </main>

  @if (count($randomBlogs))
    <section class="bg-base-200/70">
      <div class="container px-4 py-12 md:px-6 xl:px-8">
        <p class="text-pretty font-merriweather text-xl font-semibold">Artikel lain yang mungkin anda suka</p>
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          @foreach ($randomBlogs as $item)
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
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <x-slot:head>
    <meta name="description" content="{{ $blog->deskripsi_singkat }}">
    <meta name="keywords"
      content="blog pendakian, artikel pendakian, tips mendaki, pengalaman pendaki, jalur pendakian, muncak.id">
    <meta name="robots" content="index, follow">
    <link rel="alternate" href="{{ url()->current() }}" hreflang="id" />
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $blog->title }}">
    <meta property="og:description" content="{{ $blog->deskripsi_singkat }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="muncak.id">
    <meta property="og:image" content="{{ $blog->getImageUrl() }}">
    {!! $schemaOrg ?? '' !!}
  </x-slot:head>
</x-layout.app>
