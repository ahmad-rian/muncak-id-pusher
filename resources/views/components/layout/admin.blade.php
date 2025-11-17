<!DOCTYPE html>
<html class="{{ session('theme', 'winter') === 'dark-winter' ? 'dark' : '' }}"
    data-theme="{{ session('theme', 'winter') }}" lang="id" x-data="themeToggle" x-init="loadTheme()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | {{ $title ?? 'muncak.id' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="icon" type="image/png" href="{{ asset('favicon/dark/favicon-96x96.png') }}" sizes="96x96"
        media="(prefers-color-scheme: dark)" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/dark/favicon.svg') }}"
        media="(prefers-color-scheme: dark)" />
    <link rel="shortcut icon" href="{{ asset('favicon/dark/favicon.ico') }}" media="(prefers-color-scheme: dark)" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Muncak" />
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('js/maplibre-gl.css') }}">
    <link rel="stylesheet" href="{{ asset('css/trix.css') }}">

    <script defer src="{{ asset('js/alpine-persist.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine-collapse.min.js') }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/maplibre-gl.js') }}"></script>
    <script src="{{ asset('js/trix.min.js') }}"></script>
</head>

<body class="min-w-[340px]">
    <div class="{{ session('sidebar', 1) === 1 ? 'lg:drawer-open' : '' }} drawer" x-data="sidebar">
        <input class="drawer-toggle" id="sidebar-toggle" x-model="open" type="checkbox" />
        <div class="drawer-content">
            <div>

                {{-- Navbar --}}
                <nav class="border-b border-b-base-300 shadow-sm">
                    <div class="container mx-auto px-4 py-2 lg:px-6">
                        <div class="flex items-center justify-between gap-2">
                            <div class="shrink-0 grow-0">
                                <label class="btn btn-square btn-ghost btn-sm" for="sidebar-toggle">
                                    <x-gmdi-menu-r class="size-5" />
                                </label>
                            </div>
                            <div class="flex shrink-0 grow-0 items-center gap-x-2">
                                <div>
                                    <div class="btn btn-ghost btn-sm" tabindex="0" role="button"
                                        x-on:click="toggleTheme">
                                        <x-gmdi-wb-sunny-o class="size-5" x-show="theme === 'winter'" x-cloak />
                                        <x-gmdi-nights-stay-r class="size-5" x-show="theme === 'dark-winter'" x-cloak />
                                    </div>
                                </div>

                                @php
                                    $user = auth()->user();
                                @endphp

                                <div class="dropdown dropdown-end">
                                    <div class="avatar btn btn-circle btn-ghost" tabindex="0" role="button">
                                        <div class="w-10 rounded-full">
                                            <img alt="{{ "@{$user->username} Photo Profile" }}"
                                                src="{{ $user->getAvatarUrl() }}" />
                                        </div>
                                    </div>
                                    <ul class="menu dropdown-content menu-sm z-[1] mt-3 w-52 rounded-box bg-base-100 p-2 shadow"
                                        tabindex="0">
                                        @role('admin')
                                            <li><a class="justify-center" href="{{ route('index') }}">App</a></li>
                                            <li><a class="justify-center"
                                                    href="{{ route('admin.dashboard.index') }}">Admin</a></li>
                                        @endrole
                                        <li><a class="justify-center" href="{{ route('index') }}">Settings</a></li>
                                        <li>
                                            <form class="flex items-center justify-stretch" method="POST"
                                                action="{{ route('auth.sign-out') }}">
                                                @csrf
                                                <button class="w-full" type="submit">
                                                    Sign Out
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>

                {{-- Content --}}
                <section class="container mx-auto p-4 lg:p-6">
                    {{ $slot }}
                </section>

            </div>
        </div>

        {{-- Sidebar --}}
        <div class="hoverable-scrollbar drawer-side z-[101]">
            <label class="drawer-overlay" for="sidebar-toggle" aria-label="close sidebar"></label>
            <ul class="menu min-h-full w-64 border-r border-base-300 bg-base-100 p-4 text-base-content xl:w-72">
                <li>
                    <a class="btn btn-ghost flex items-center gap-x-1" href="{{ route('admin.dashboard.index') }}">
                        <img class="size-[48px] object-contain object-center"
                            src="{{ asset('img/logo/logo-black-remove.png') }}" alt="logo">
                        <span class="font-merriweather text-xl font-semibold">muncak.id</span>
                    </a>
                </li>

                <li class="mt-4">
                    <a class="{{ routeActive() }}" href="{{ route('admin.dashboard.index') }}">
                        <x-gmdi-grid-view-r class="size-5" />
                        Dashboard
                    </a>
                </li>

                <li class="menu-title mt-4 uppercase">Gunung dan Rute</li>
                <li>
                    <a class="{{ routeActive('gunung') }}" href="{{ route('admin.gunung.index') }}">
                        <x-gmdi-terrain-r class="size-5" />
                        Gunung
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('rute') }}" href="{{ route('admin.rute.index') }}">
                        <x-gmdi-route-r class="size-5" />
                        Rute
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('point') }}" href="{{ route('admin.point.index') }}">
                        <x-gmdi-push-pin-r class="size-5" />
                        Point
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('rute-tingkat-kesulitan') }}"
                        href="{{ route('admin.rute-tingkat-kesulitan.index') }}">
                        <x-gmdi-bar-chart-r class="size-5" />
                        Tingkat Kesulitan
                    </a>
                </li>

                <li class="menu-title mt-4 uppercase">Wilayah</li>
                <li>
                    <a class="{{ routeActive('negara') }}" href="{{ route('admin.negara.index') }}">
                        <x-gmdi-my-location-r class="size-5" />
                        Negara
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('provinsi') }}" href="{{ route('admin.provinsi.index') }}">
                        <x-gmdi-map-r class="size-5" />
                        Provinsi
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('kabupaten-kota') }}" href="{{ route('admin.kabupaten-kota.index') }}">
                        <x-gmdi-location-city-r class="size-5" />
                        Kabupaten/Kota
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('kecamatan') }}" href="{{ route('admin.kecamatan.index') }}">
                        <x-gmdi-home-work-r class="size-5" />
                        Kecamatan
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('desa') }}" href="{{ route('admin.desa.index') }}">
                        <x-gmdi-home-r class="size-5" />
                        Desa
                    </a>
                </li>

                <li class="menu-title mt-4 uppercase">Pengguna</li>
                <li>
                    <a class="{{ routeActive('user') }}" href="{{ route('admin.user.index') }}">
                        <x-gmdi-account-box-r class="size-5" />
                        User
                    </a>
                </li>
                <li>
                    <a class="{{ routeActive('role') }}" href="{{ route('admin.role.index') }}">
                        <x-gmdi-switch-account-r class="size-5" />
                        Role
                    </a>
                </li>

                <li class="menu-title mt-4 uppercase">Interaksi</li>
                <li>
                    <a class="{{ routeActive('comment') }}" href="{{ route('admin.comment.index') }}">
                        <x-gmdi-chat-r class="size-5" />
                        Ulasan
                    </a>
                    <a class="{{ routeActive('visitor') }}" href="{{ route('admin.visitor.index') }}">
                        <x-gmdi-supervised-user-circle-r class="size-5" />
                        Pengunjung
                    </a>
                    <a class="{{ routeActive('blog') }}" href="{{ route('admin.blog.index') }}">
                        <x-gmdi-article-r class="size-5" />
                        Artikel
                    </a>
                    <a class="{{ routeActive('live-stream') }}" href="{{ route('admin.live-stream.index') }}">
                        <x-gmdi-videocam-r class="size-5" />
                        Live Streaming
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <script src="{{ asset('js/chart-plugin-datalabels.min.js') }}"></script>

    <script>
        document.addEventListener("trix-initialize", function(event) {
            var editor = event.target;
            editor
                .toolbarElement
                .querySelector("[data-trix-button-group='file-tools']")
                .style
                .display = 'none';
        });
    </script>

    <x-toast></x-toast>

    {{ $js ?? '' }}

    <script title="alpine.js">
        function sidebar() {
            return {
                open: {{ session('sidebar', 1) === 1 ? 'true' : 'false' }},

                init() {
                    const $el = $(this.$el);
                    this.toggle($el, this.open);
                    this.$watch('open', (value) => {
                        if (window.innerWidth >= 1024) {
                            this.toggle($el, value);
                            this.update();
                        }
                    });
                },

                toggle($el, isOpen) {
                    if (window.innerWidth >= 1024) {
                        $el.toggleClass('lg:drawer-open', isOpen);
                    } else {
                        $el.removeClass('lg:drawer-open');
                        this.open = false;
                    }
                },

                update() {
                    $.ajax({
                        url: "{{ route('admin.api.toggle-sidebar') }}",
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        }),
                    });
                }
            }
        }

        function themeToggle() {
            return {
                theme: "{{ session('theme', 'winter') }}",

                loadTheme() {
                    document.documentElement.setAttribute('data-theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark-winter');
                },

                toggleTheme() {
                    this.theme = this.theme === 'winter' ? 'dark-winter' : 'winter';
                    document.documentElement.setAttribute('data-theme', this.theme);
                    document.documentElement.classList.toggle('dark', this.theme === 'dark-winter');

                    $.ajax({
                        url: "{{ route('user.toggle-theme') }}",
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            theme: this.theme
                        }),
                    });
                },
            }
        }
    </script>
</body>

</html>
