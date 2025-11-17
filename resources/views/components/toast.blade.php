<div class="md:toast-right toast toast-top z-[9999] min-w-full whitespace-normal md:min-w-[28rem]" id="aa-toast" x-data
  x-show="$store.toast.toast.length" x-cloak>
  <template x-if="$store.toast.toast.length">
    <template x-for="(item, i) in $store.toast.toast" :key="item.id">
      <div class="alert w-full grid-flow-col border-base-300 text-start text-sm shadow-md" role="alert"
        x-init="$store.toast.autoClose(item.id)">
        <svg class="size-6 text-success" x-show="item.type == 'success'" x-cloak xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24">
          <path fill="currentColor"
            d="m10.6 16.2l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h14q.825 0 1.413.588T21 5v14q0 .825-.587 1.413T19 21z" />
        </svg>
        <svg class="size-6 text-warning" x-show="item.type == 'warning'" x-cloak xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24">
          <path fill="currentColor"
            d="M2.725 21q-.275 0-.5-.137t-.35-.363t-.137-.488t.137-.512l9.25-16q.15-.25.388-.375T12 3t.488.125t.387.375l9.25 16q.15.25.138.513t-.138.487t-.35.363t-.5.137zM12 18q.425 0 .713-.288T13 17t-.288-.712T12 16t-.712.288T11 17t.288.713T12 18m0-3q.425 0 .713-.288T13 14v-3q0-.425-.288-.712T12 10t-.712.288T11 11v3q0 .425.288.713T12 15" />
        </svg>
        <svg class="size-6 text-info" x-show="item.type == 'info'" x-cloak xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24">
          <path fill="currentColor"
            d="M11 17h2v-6h-2zm1-8q.425 0 .713-.288T13 8t-.288-.712T12 7t-.712.288T11 8t.288.713T12 9m0 13q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22" />
        </svg>
        <svg class="size-6 text-error" x-show="item.type == 'error'" x-cloak xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24">
          <path fill="currentColor"
            d="M9.075 21q-.4 0-.762-.15t-.638-.425l-4.1-4.1q-.275-.275-.425-.638T3 14.926v-5.85q0-.4.15-.762t.425-.638l4.1-4.1q.275-.275.638-.425T9.075 3h5.85q.4 0 .763.15t.637.425l4.1 4.1q.275.275.425.638t.15.762v5.85q0 .4-.15.763t-.425.637l-4.1 4.1q-.275.275-.638.425t-.762.15zM12 13.4l2.15 2.15q.275.275.7.275t.7-.275t.275-.7t-.275-.7L13.4 12l2.15-2.15q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275L12 10.6L9.85 8.45q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L10.6 12l-2.15 2.15q-.275.275-.275.7t.275.7t.7.275t.7-.275z" />
        </svg>
        <div>
          <h3 class="font-merriweather font-semibold" x-text="item.title" x-show="item.title" x-cloak></h3>
          <div class="text-xs" x-text="item.message" x-show="item.message" x-cloak></div>
        </div>
        <button class="btn btn-square btn-ghost btn-xs" x-on:click="$store.toast.remove(item.id)">
          <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="currentColor"
              d="m12 13.4l-4.9 4.9q-.275.275-.7.275t-.7-.275t-.275-.7t.275-.7l4.9-4.9l-4.9-4.9q-.275-.275-.275-.7t.275-.7t.7-.275t.7.275l4.9 4.9l4.9-4.9q.275-.275.7-.275t.7.275t.275.7t-.275.7L13.4 12l4.9 4.9q.275.275.275.7t-.275.7t-.7.275t-.7-.275z" />
          </svg>
        </button>
      </div>
    </template>
  </template>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
      toast: [],
      type: "{{ session('toast')['type'] ?? '' }}",
      title: "{{ session('toast')['title'] ?? '' }}",
      message: "{{ session('toast')['message'] ?? '' }}",

      generateId() {
        return '_' + Math.random().toString(36).substr(2, 9);
      },

      toggle() {
        if (this.type && (this.title || this.message)) {
          this.toast.push({
            id: this.generateId(),
            type: this.type,
            title: this.title ?? null,
            message: this.message ?? null,
          });
        }
      },

      push({
        type = null,
        title = null,
        message = null
      }) {
        if (type && (title || message)) {
          this.toast.push({
            id: this.generateId(),
            type,
            title,
            message
          });
        }
      },

      autoClose(id) {
        setTimeout(() => {
          this.remove(id);
        }, 3000);
      },

      remove(id) {
        this.toast = this.toast.filter(item => item.id !== id);
      }
    });

    Alpine.store('toast').toggle();

    // Alpine.store('toast').push({
    //   type: 'success',
    //   title: 'Aaa',
    //   message: 'Aaa'
    // })
    // Alpine.store('toast').push({
    //   type: 'warning',
    //   title: 'Aaa',
    //   message: 'Aaa'
    // })
    // Alpine.store('toast').push({
    //   type: 'info',
    //   title: 'Aaa',
    //   message: 'Aaa'
    // })
    // Alpine.store('toast').push({
    //   type: 'error',
    //   title: 'Aaa',
    //   message: 'Aaa'
    // })
    // Alpine.store('toast').push({
    //   type: 'success',
    //   title: 'Aaa',
    //   message: 'Aaa'
    // })
  });
</script>
<script type="module">
  import autoAnimate from "{{ asset('js/auto-animate.min.js') }}"
  document.addEventListener('alpine:init', () => {
    autoAnimate(document.getElementById('aa-toast'))
  });
</script>
