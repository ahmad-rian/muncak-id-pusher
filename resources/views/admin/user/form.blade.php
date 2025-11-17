<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.user.index') }}">User</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit')
        <li>{{ $data->kode }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} User</p>
  </div>

  <form class="mt-6" action="{{ $route }}" method="post">
    @csrf
    @method($type == 'Edit' ? 'PUT' : 'POST')

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <x-form.input type="text" name="name" label="Nama" :value="old('name', $data->name)" required />
      <x-form.input type="email" name="email" label="Email" :value="old('email', $data->email)" required />

      @if ($type == 'Create')
        <x-form.input type="password" name="password" label="Password" required />
        <x-form.input type="password" name="password_confirmation" label="Konfirmasi Password" required />
        <x-form.select-option name="role_id" label="Role" :value="old('role_id')" :option="$role" />
      @endif

      <x-form.input type="text" name="username" label="Username" :value="old('username', $data->username)" />
      <x-form.input type="text" name="bio" label="Bio" :value="old('bio', $data->bio)" />

      @if ($type == 'Edit' && $data->password)
        <div class="mt-4 md:col-span-2">
          <div>
            <p class="text-sm font-medium md:col-span-2">Perubahan Password</p>
            <p class="text-sm text-base-content/70">Kosongkan semua kolom dibawah ini jika tidak ingin mengganti
              password</p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <x-form.input type="password" name="old_password" label="Password Lama"
              placeholder="Kosongkan jika tidak ingin mengganti password" />
            <x-form.input type="password" name="new_password" label="Password Baru"
              placeholder="Kosongkan jika tidak ingin mengganti password" />
            <x-form.input type="password" name="new_password_confirmation" label="Konfirmasi Password Baru"
              placeholder="Kosongkan jika tidak ingin mengganti password" />
          </div>
        </div>
      @endif
    </div>

    <div class="mt-4 flex flex-row-reverse items-center justify-end gap-2">
      <button class="btn btn-success btn-sm" type="submit">Submit</button>
      <a class="btn btn-neutral btn-sm" href="{{ route('admin.user.index') }}">Cancel</a>
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
