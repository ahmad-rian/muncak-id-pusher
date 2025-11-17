<x-layout.admin>
  <div class="breadcrumbs text-sm text-base-content/70">
    <ul>
      <li><a href="{{ route('admin.rute.index') }}">Rute</a></li>
      <li>{{ $type }}</li>
      @if ($type == 'Edit' || $type == 'Show')
        <li>{{ $data->id }}</li>
      @endif
    </ul>
  </div>

  <div class="flex justify-between gap-4 border-b border-base-300 pb-4">
    <p class="text-2xl font-semibold">{{ $type }} Rute</p>
  </div>

  <div class="mt-6 space-y-6" id="auto-animate-1" x-data="{ tabs: $persist('data').as('admin-rute-show') }">
    <div class="mx-auto max-w-xs">
      <div class="tabs-boxed tabs" role="tablist">
        <a class="tab" role="tab" x-on:click="tabs = 'data'"
          :class="tabs == 'data' &&
              'text-neutral-content bg-[var(--fallback-b2,oklch(var(--n)/var(--tw-bg-opacity)))] dark:bg-[var(--fallback-b2,oklch(var(--p)/var(--tw-bg-opacity)))]'">
          Data
        </a>
        <a class="tab" role="tab" x-on:click="tabs = 'point'"
          :class="tabs == 'point' &&
              'text-neutral-content bg-[var(--fallback-b2,oklch(var(--n)/var(--tw-bg-opacity)))] dark:bg-[var(--fallback-b2,oklch(var(--p)/var(--tw-bg-opacity)))]'">
          Point
        </a>
      </div>
    </div>

    <template x-if="tabs == 'data'">
      <div>
        <x-admin.rute.show.tab-data :data="$data"></x-admin.rute.show.data>
      </div>
    </template>

    <template x-if="tabs == 'point'">
      <div>
        <x-admin.rute.show.tab-point :data="$data"></x-admin.rute.show.table-point>
      </div>
    </template>
  </div>

  <x-slot:js>
  </x-slot:js>
</x-layout.admin>
