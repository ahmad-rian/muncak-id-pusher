<x-layout.admin>
  <p class="border-b border-base-300 pb-4 text-xl font-semibold">Dashboard</p>

  {{-- Statistics --}}
  <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
    <div class="card card-compact rounded-md border border-base-300 transition-shadow hover:shadow">
      <div class="card-body">
        <p class="font-merriweather text-lg text-base-content/70">Total Gunung/Rute</p>
        <p class="text-xl font-medium">999</p>
      </div>
    </div>
    <div class="card card-compact rounded-md border border-base-300 transition-shadow hover:shadow">
      <div class="card-body">
        <p class="font-merriweather text-lg text-base-content/70">Total User</p>
        <p class="text-xl font-medium">999</p>
      </div>
    </div>
    <div class="card card-compact rounded-md border border-base-300 transition-shadow hover:shadow">
      <div class="card-body">
        <p class="font-merriweather text-lg text-base-content/70">Total Komentar</p>
        <p class="text-xl font-medium">999</p>
      </div>
    </div>
    <div class="card card-compact rounded-md border border-base-300 transition-shadow hover:shadow">
      <div class="card-body">
        <p class="font-merriweather text-lg text-base-content/70">-</p>
        <p class="text-xl font-medium">-</p>
      </div>
    </div>
  </div>

  {{-- App Info --}}
  <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="card card-compact rounded-md border border-base-300">
      <div class="card-body">
        <div class="flex items-center justify-between gap-4">
          <div class="flex shrink grow items-center gap-4">
            <div class="size-12 shrink-0 grow-0">
              <svg class="size-full rounded-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor"
                  d="M5.85 17.1q1.275-.975 2.85-1.537T12 15t3.3.563t2.85 1.537q.875-1.025 1.363-2.325T20 12q0-3.325-2.337-5.663T12 4T6.337 6.338T4 12q0 1.475.488 2.775T5.85 17.1M12 13q-1.475 0-2.488-1.012T8.5 9.5t1.013-2.488T12 6t2.488 1.013T15.5 9.5t-1.012 2.488T12 13m0 9q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22" />
              </svg>
            </div>
            <div class="shrink grow">
              <p class="font-merriweather text-lg font-medium">{{ Auth::user()->name }}</p>
              <p class="text-base-content/70">{{ Auth::user()->email }}</p>
            </div>
          </div>
          <div>
            <form action="{{ route('auth.sign-out') }}" method="post">
              @csrf
              <button class="btn btn-neutral btn-sm" type="submit">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path fill="currentColor"
                    d="M0 23V3q0-.825.588-1.412T2 1h7q.825 0 1.413.588T11 3v20H9v-2H2v2zm15.5 0v-6.6l2.15-2.05l-.525-2.6q-.975 1.125-2.363 1.688T12 14v-2q1.2 0 2.325-.575t1.85-1.75l.75-1.225q.375-.625 1.1-.85t1.375.05L24 9.6v4.9h-2v-3.575l-1.425-.6L23 23h-2.05l-1.525-7.15l-1.925 1.8V23zM4 13l3.5-2L4 9zm13-6q-.825 0-1.412-.587T15 5t.588-1.412T17 3t1.413.588T19 5t-.587 1.413T17 7" />
                </svg>
                Sign Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-compact rounded-md border border-base-300">
      <div class="card-body">
        <div class="flex items-center justify-between gap-4">
          <div class="flex shrink grow items-center gap-4">
            <div class="size-12 shrink-0 grow-0">
              <svg class="size-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path fill="currentColor"
                  d="M12 17q.425 0 .713-.288T13 16v-4q0-.425-.288-.712T12 11t-.712.288T11 12v4q0 .425.288.713T12 17m0-8q.425 0 .713-.288T13 8t-.288-.712T12 7t-.712.288T11 8t.288.713T12 9m0 13q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22" />
              </svg>
            </div>
            <div class="shrink grow">
              <p class="font-merriweather text-lg font-medium">muncak.id</p>
              <p class="italic text-base-content/70">v 1.0.0</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-layout.admin>
