<!DOCTYPE html>
<html class="{{ session('theme', 'winter') === 'dark-winter' ? 'dark' : '' }}"
    data-theme="{{ session('theme', 'winter') }}" lang="id" x-data="themeToggle" x-init="loadTheme()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>muncak.id | {{ $title ?? 'Rencanakan Destinasi Pendakianmu' }}</title>
    {{ $head ?? '' }}

    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-dark.ico') }}" sizes="96x96"
        media="(prefers-color-scheme: dark)" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon-dark.svg') }}"
        media="(prefers-color-scheme: dark)" />
    <link rel="shortcut icon" href="{{ asset('favicon/favicon-dark.ico') }}" media="(prefers-color-scheme: dark)" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Muncak" />
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>

    <script defer src="{{ asset('js/alpine-persist.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine-collapse.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>

<body class="overflow-x-hidden scroll-smooth font-lato transition-all">
    <nav class="fixed inset-x-0 top-0 z-50 bg-base-100 shadow">
        <div class="navbar mx-auto bg-base-100">
            <div class="navbar-start">
                <a class="btn btn-ghost flex items-center gap-x-1" href="/">
                    <img class="size-[48px] object-contain object-center dark:brightness-0 dark:invert"
                        src="{{ asset('img/logo/logo-2.png') }}" alt="logo">
                    <span class="font-merriweather text-xl">muncak.id</span>
                </a>
            </div>

            <div class="navbar-center hidden md:flex">
                <ul class="menu menu-horizontal gap-x-2">
                    <li><a class="btn btn-ghost btn-sm" href="{{ route('index') }}">Home</a></li>
                    <li><a class="btn btn-ghost btn-sm" href="{{ route('jelajah.index') }}">Jelajah</a></li>
                    <li><a class="btn btn-ghost btn-sm" href="{{ route('blog.index') }}">Artikel</a></li>
                    <li>
                        <a class="btn btn-ghost btn-sm gap-1" href="{{ route('live-cam.index') }}">
                            <x-gmdi-videocam-r class="size-4" />
                            Webcams
                        </a>
                    </li>
                </ul>
            </div>

            <div class="navbar-end">
                <x-mobile-navbar />

                <ul class="hidden items-center gap-x-2 px-1 md:flex">
                    <li>
                        <div class="btn btn-ghost btn-sm" tabindex="0" role="button" x-on:click="toggleTheme">
                            <x-gmdi-wb-sunny-o class="size-5" x-show="theme === 'winter'" x-cloak />
                            <x-gmdi-nights-stay-r class="size-5" x-show="theme === 'dark-winter'" x-cloak />
                        </div>
                    </li>

                    @guest
                        <li>
                            <a class="btn btn-outline btn-primary btn-sm" href="{{ route('auth.sign-in') }}">
                                Masuk
                            </a>
                        </li>
                        <li>
                            <a class="btn btn-primary btn-sm" href="{{ route('auth.sign-up') }}">
                                Daftar
                            </a>
                        </li>
                    @endguest

                    @auth
                        @php
                            $user = auth()->user();
                        @endphp

                        <div class="dropdown dropdown-end">
                            <div class="avatar btn btn-circle btn-ghost" tabindex="0" role="button">
                                <div class="w-10 rounded-full">
                                    <img alt="{{ "@{$user->username} Photo Profile" }}" src="{{ $user->getAvatarUrl() }}" />
                                </div>
                            </div>
                            <ul class="menu dropdown-content menu-sm z-[1] mt-3 w-52 rounded-box bg-base-100 p-2 shadow"
                                tabindex="0">
                                @role('admin')
                                <li>
                                    <a href="{{ route('index') }}">
                                        <x-gmdi-desktop-windows-r class="size-4" />
                                        App
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.dashboard.index') }}">
                                        <x-gmdi-admin-panel-settings-r class="size-4" />
                                        Admin
                                    </a>
                                </li>
                                @endrole
                                <li>
                                    <a href="{{ route('index') }}">
                                        <x-gmdi-home-r class="size-4" />
                                        Home
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('jelajah.index') }}">
                                        <x-gmdi-route-r class="size-4" />
                                        Jelajah
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('blog.index') }}">
                                        <x-gmdi-article-r class="size-4" />
                                        Artikel
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.index') }}">
                                        <x-gmdi-person-r class="size-4" />
                                        Profile
                                    </a>
                                </li>
                                <li>
                                    <form class="flex items-stretch" method="POST" action="{{ route('auth.sign-out') }}">
                                        @csrf
                                        <button class="flex w-full items-center gap-x-2" type="submit">
                                            <x-gmdi-exit-to-app-r class="size-4" />
                                            Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{ $slot }}

    <footer class="bg-gray-800 text-white dark:bg-base-300">
        <div class="container mx-auto px-4 pt-14 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
            <a class="flex items-center gap-x-2" href="{{ route('index') }}">
                <img class="size-14 object-contain object-center brightness-0 invert filter"
                    src="{{ asset('img/logo/logo-2.png') }}" alt="logo">
                <span class="font-merriweather text-xl font-semibold">muncak.id</span>
            </a>
            <div class="mt-2 grid grid-cols-1 gap-4 pb-8 lg:grid-cols-2">
                <div>
                    <p class="max-w-sm text-base-100/90 dark:text-base-content">
                        Menyajikan informasi terintegrasi bagi para pendaki yang menginginkan kemudahan dalam
                        merencanakan pendakian
                        gunung dan penjelajahan pegunungan di Indonesia dan luar negeri
                    </p>
                </div>
                <div class="lg:ml-auto lg:max-w-lg">
                    <div class="flex items-center gap-x-2 text-balance text-base-100 dark:text-base-content">
                        <x-gmdi-pin-drop-r class="size-5 shrink-0 grow-0" />
                        <p>
                            Pusat Pengembangan Teknologi Petualangan Tropis. Laboratorium Teknik Industri, Teknik
                            Geologi dan
                            Informatika Universitas Jenderal
                            Soedirman
                        </p>
                    </div>
                    <div class="mt-3 flex items-center gap-x-2 text-base-100 dark:text-base-content">
                        <x-gmdi-mail-r class="size-5 shrink-0 grow-0" />
                        <a class="hover:underline"
                            href="mailto:laboratoriumindustri@unsoed.ac.id">laboratoriumindustri@unsoed.ac.id</a>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-3">
                <div>
                    <p class="text-xl font-semibold">Telusuri</p>
                    <div class="mt-3 space-y-1 text-lg text-base-100 *:py-1 dark:text-base-content">
                        <div>
                            <a class="hover:underline" href="{{ route('index') }}">Home</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="{{ route('jelajah.index') }}">Jelajah</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="{{ route('blog.index') }}">Artikel</a>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-xl font-semibold">Akun</p>
                    <div class="mt-3 space-y-1 text-lg text-base-100 *:py-1 dark:text-base-content">
                        <div>
                            <a class="hover:underline" href="{{ route('auth.sign-up') }}">Daftar</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="{{ route('auth.sign-in') }}">Masuk</a>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-xl font-semibold">Komunitas</p>
                    <div class="mt-3 space-y-1 text-lg text-base-100 *:py-1 dark:text-base-content">
                        <div>
                            <a class="hover:underline" href="https://www.instagram.com/muncakdotid.official"
                                target="_blank">Instagram</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="/" target="_blank">Facebook</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="/" target="_blank">X</a>
                        </div>
                        <div>
                            <a class="hover:underline" href="/" target="_blank">Discord</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="container mx-auto mt-4 border-t border-base-content px-4 py-6 dark:border-base-300 md:px-6 lg:px-8 xl:px-12 2xl:px-16">
            <p class="text-center text-sm text-base-200/80 dark:text-base-content md:text-start">
                Copyright 2024 © Muncak ID All Rights Reserved
            </p>
        </div>
        </div>
    </footer>

    <!-- Defer heavy libraries to not block rendering -->
    <script defer src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script defer src="{{ asset('js/chart.min.js') }}"></script>
    <script defer src="{{ asset('js/chart-plugin-datalabels.min.js') }}"></script>
    <script defer src="{{ asset('js/lodash.min.js') }}"></script>

    <x-toast></x-toast>

    {{ $js ?? '' }}

    <script title="alpine.js">
        function themeToggle() {
            return {
                theme: '{{ session('theme', 'winter') }}',

                loadTheme() {
                    document.documentElement.setAttribute('data-theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark-winter');
                },

                toggleTheme() {
                    this.theme = this.theme === 'winter' ? 'dark-winter' : 'winter';
                    document.documentElement.setAttribute('data-theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark-winter');

                    // Use fetch instead of jQuery to avoid dependency
                    fetch("{{ route('user.toggle-theme') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            theme: this.theme
                        })
                    }).catch(err => console.error('Theme toggle failed:', err));
                },
            }
        }
    </script>
</body>

</html>
