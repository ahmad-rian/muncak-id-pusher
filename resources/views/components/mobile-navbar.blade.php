<div class="dropdown dropdown-end dropdown-bottom md:hidden">
    <div class="btn btn-ghost lg:hidden" tabindex="0" role="button">
        <x-gmdi-menu-r class="size-5" />
    </div>

    <ul class="dropdown-content menu menu-sm z-[1] mt-3 w-52 space-y-2 rounded-box bg-base-100 p-2 shadow" tabindex="0">
        <div class="btn btn-ghost btn-sm" tabindex="0" role="button" x-on:click="toggleTheme">
            <x-gmdi-wb-sunny-o class="size-5" x-show="theme === 'winter'" x-cloak />
            <x-gmdi-nights-stay-r class="size-5" x-show="theme === 'dark-winter'" x-cloak />
        </div>
        <li><a class="justify-center" href="{{ route('index') }}">Home</a></li>
        <li><a class="justify-center" href="{{ route('jelajah.index') }}">Jelajah</a></li>
        <li><a class="justify-center" href="{{ route('blog.index') }}">Artikel</a></li>
        <li>
            <a class="justify-center flex items-center gap-2" href="{{ route('live-cam.index') }}">
                <x-gmdi-videocam-r class="size-4" />
                Webcams
            </a>
        </li>
        @auth
            @role('admin')
                <li><a class="justify-center" href="{{ route('index') }}">App</a></li>
                <li><a class="justify-center" href="{{ route('admin.dashboard.index') }}">Admin</a></li>
            @endrole
            <li><a class="justify-center" href="{{ route('profile.index') }}">Profile</a></li>
            <li>
                <form class="flex items-stretch" method="POST" action="{{ route('auth.sign-out') }}">
                    @csrf
                    <button class="w-full" type="submit">
                        Sign Out
                    </button>
                </form>
            </li>
        @endauth
        @guest
            <li><a class="btn btn-outline btn-primary btn-sm" href="{{ route('auth.sign-in') }}">Masuk</a></li>
            <li><a class="btn btn-primary btn-sm" href="{{ route('auth.sign-up') }}">Daftar</a></li>
        @endguest
    </ul>
</div>
