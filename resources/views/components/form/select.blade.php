@props(['route', 'name' => '', 'label' => '', 'value' => null, 'required' => null, 'readonly' => false])

<div x-data="{{ $name }}" x-on:click.outside="open = false" x-on:keydown.enter.prevent="">
  <div class="label">
    <label class="{{ $required ? 'required' : null }} label-text font-medium" for="{{ $name }}">
      {{ $label }}
    </label>
  </div>

  @if (!$readonly)
    <div class="relative" x-on:focus.outside.stop="open = false" x-on:keydown.escape.stop="open = false">
      <input type="hidden" name="{{ $name }}" x-model="value">

      <input class="@error($name) {{ 'input-error' }} @enderror input input-sm input-bordered w-full"
        id="{{ $name }}" readonly placeholder="{{ $label }}" type="text" :value="label"
        x-on:click="open = true" x-on:focus.stop="open = true">

      <div
        class="absolute top-[120%] z-[1] w-full cursor-pointer overflow-hidden rounded-md border border-base-300 bg-base-100"
        x-show="open" x-cloak x-collapse>
        <div class="border-b border-base-300">
          <input class="h-8 w-full px-3 text-sm focus:outline-none" id="{{ "search_{$name}" }}" placeholder="Search..."
            type="text" x-ref="search" x-model.debounce.500ms="search">
        </div>

        <ul class="max-h-[10rem] overflow-y-auto overflow-x-hidden" id="{{ "aa_{$name}" }}">
          <template x-if="data.length">
            <template x-for="item in data" :key="item.id">
              <li x-on:keydown.arrow-up.prevent.stop="focus('prev')"
                x-on:keydown.arrow-down.prevent.stop="focus('next')">
                <button
                  class="btn btn-ghost btn-sm btn-block line-clamp-1 flex animate-none justify-between gap-2 rounded-none text-start font-normal focus:bg-base-300 focus:outline-none"
                  type="button"
                  x-on:click="() => {
                    value = item.id;
                    open = false;
                  }"
                  :class="value == item.id && 'btn-active'">
                  <span class="shrink grow" x-text="item.label"></span>
                  <span class="shrink-0 grow-0" x-show="value == item.id">✓</span>
                </button>
              </li>
            </template>
          </template>

          <template x-if="!data.length">
            <p class="p-2 text-center text-sm text-base-content/70">No result match your search.</p>
          </template>
        </ul>
      </div>
    </div>
  @endif

  @if ($readonly)
    <div>
      <input class="@error($name) {{ 'input-error' }} @enderror input input-sm input-bordered w-full"
        id="{{ $name }}" readonly placeholder="{{ $label }}" type="text" :value="label">
    </div>
  @endif

  @error($name)
    <div class="label">
      <span class="label-text-alt text-error">{{ $message }}</span>
    </div>
  @enderror
</div>

<script title="alpine.js">
  function {{ $name }}() {
    return {
      open: false,
      loading: false,
      data: [],
      value: "{{ $value }}",
      label: "",
      search: "",
      init() {
        this.getData()
        this.$watch('search', () => {
          this.value = "";
          this.getData();
        })
        this.$watch('value', (value) => {
          const selected = this.data.find(item => item.id == value);
          this.label = selected ? selected.label : "";
        })
        this.$watch('open', (value) => {
          if (value) this.$nextTick(() => {
            this.$refs.search.focus();
          });
        })
      },
      getData() {
        this.loading = true;
        $.post({
          url: "{{ $route }}",
          contentType: 'application/json',
          data: JSON.stringify({
            _token: $('meta[name="csrf-token"]').attr('content'),
            search: this.search,
            value: this.value,
          }),
          success: (data) => {
            this.data = data;
            if (this.value) this.label = this.data.find(item => item.id == this.value)?.label;
          },
          complete: () => {
            this.loading = false;
          }
        })
      },
      focus(direction) {
        const currentLi = $(this.$el).closest('li');
        let targetLi;

        if (direction === 'next') {
          targetLi = currentLi.next('li');
          if (targetLi.length === 0) targetLi = $(this.$el).closest('ul').find('li').first();
        } else if (direction === 'prev') {
          targetLi = currentLi.prev('li');
          if (targetLi.length === 0) targetLi = $(this.$el).closest('ul').find('li').last();
        }

        targetLi.find('button').focus();
      },
    }
  }
</script>
@if (!$readonly)
  <script type="module">
    import autoAnimate from "{{ asset('js/auto-animate.min.js') }}"
    $(function() {
      autoAnimate(document.getElementById('{{ "aa_{$name}" }}'))
    })
  </script>
@endif
