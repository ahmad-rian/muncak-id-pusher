<div class="absolute right-2 top-2">
  <div class="dropdown-end dropdown">
    <div class="tooltip tooltip-left" data-tip="Map Styles">
      <button class="btn btn-square btn-sm" type="button">
        <x-gmdi-map-r class="size-6" />
      </button>
    </div>
    <div class="dropdown-content z-[1] flex items-center gap-2 rounded-box bg-base-100 p-2 shadow">
      <div class="tooltip tooltip-left" data-tip="Outdoor">
        <button class="btn btn-square btn-xs" type="button" x-on:click="mapStyle = 'outdoor'"
          :class="mapStyle == 'outdoor' && 'btn-active'">
          <x-gmdi-maps-home-work-r class="size-4" />
        </button>
      </div>
      <div class="tooltip tooltip-left" data-tip="OpenStreetMap">
        <button class="btn btn-square btn-xs" type="button" x-on:click="mapStyle = 'openstreetmap'"
          :class="mapStyle == 'openstreetmap' && 'btn-active'">
          <x-gmdi-streetview-r class="size-4" />
        </button>
      </div>
      <div class="tooltip tooltip-left" data-tip="Satellite">
        <button class="btn btn-square btn-xs" type="button" x-on:click="mapStyle = 'satellite'"
          :class="mapStyle == 'satellite' && 'btn-active'">
          <x-gmdi-satellite-alt-r class="size-4" />
        </button>
      </div>
    </div>
  </div>
</div>
