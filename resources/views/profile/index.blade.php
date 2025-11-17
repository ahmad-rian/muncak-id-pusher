<x-layout.app>
  <x-slot:title>{{ 'Profile' }}</x-slot:title>

  <div class="container mx-auto px-4 pb-12 pt-20 md:px-6 xl:px-8">
    <div class="no-scrollbar breadcrumbs text-sm">
      <ul>
        <li><a href="/">Home</a></li>
        <li class="font-bold">Profile</li>
      </ul>
    </div>

    <div class="mt-6 grid grid-cols-12 gap-6">
      <div class="col-span-12 lg:col-span-3">
        <ul class="menu menu-md w-full rounded-box bg-base-200">
          <li>
            <a class="{{ request()->routeIs('profile.index') ? 'active' : '' }}" href="{{ route('profile.index') }}">
              <x-gmdi-person-r class="size-4" />
              Profile
            </a>
          </li>
          <li>
            <a class="{{ request()->routeIs('profile.ulasan') ? 'active' : '' }}" href="{{ route('profile.ulasan') }}">
              <x-gmdi-chat-r class="size-4" />
              Ulasan
            </a>
          </li>
        </ul>
      </div>

      <div class="col-span-12 lg:col-span-9">
        <div class="max-w-screen-sm">
          <div class="border-b border-base-300 font-merriweather">
            <span class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold">
              Informasi Akun
            </span>
          </div>
          <form class="mt-4" action="{{ route('profile.update') }}" method="POST">
            @csrf
            <div class="space-y-4">
              <x-form.input name="name" label="Nama" :value="old('name', auth()->user()->name)" />
              <x-form.input name="username" label="Username" :value="old('username', auth()->user()->username)" />
              <x-form.input name="email" label="Email" :value="auth()->user()->email" readonly />
              @php
                $providerValue = $provider->isNotEmpty() ? $provider->pluck('provider')->implode(', ') : 'email';
              @endphp
              <x-form.input name="provider" label="Provider" :value="$providerValue" readonly />
              <button class="btn btn-primary btn-sm">Simpan</button>
            </div>
          </form>
        </div>

        <div class="mt-6 max-w-screen-sm">
          <div class="border-b border-base-300 font-merriweather">
            <span class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold">
              Foto Profil
            </span>
          </div>
          <form class="mt-4" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
              <x-form.image name="avatar" label="Avatar" :value="auth()->user()->getAvatarUrl()" />
              <button class="btn btn-primary btn-sm">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-layout.app>
