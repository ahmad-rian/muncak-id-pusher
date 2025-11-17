@props(['routeList', 'routeAction', 'name'])

<div x-data="table_{{ $name }}">
  <div class="flex flex-col items-center justify-end gap-4 border-b border-base-300 p-4 md:flex-row">
    <span class="loading loading-spinner" x-show="loading"></span>
    <div class="join">
      <p class="btn join-item btn-sm">Per Page:</p>
      <select class="join-item select select-bordered select-sm" id="take_{{ $name }}" x-model="take">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
      </select>
    </div>

    <label class="input input-sm input-bordered flex w-full max-w-sm items-center gap-2" for="search">
      <x-gmdi-search-r class="size-5" />
      <input class="grow" id="search" x-model.debounce.500ms="search" type="text" name="search"
        placeholder="Search..." />
    </label>
  </div>

  <template x-if="!error">
    <div class="hoverable-scrollbar overflow-x-auto">
      <div class="min-w-[30rem]" :class="loading && 'opacity-35 pointer-events-none'" x-html="data"></div>
    </div>
  </template>

  <template x-if="error">
    <div class="p-4">
      <div class="alert" role="alert">
        <x-gmdi-warning-r class="size-6 text-error" />
        <div>
          <h3 class="font-bold">Server Error!</h3>
          <div class="text-xs">An unexpected error occurred. Please try again later or contact support.</div>
        </div>
      </div>
    </div>
  </template>

  <div class="flex flex-col items-center justify-between gap-2 border-t border-base-300 p-4 md:flex-row">
    <p class="text-sm text-base-content/70">
      Showing <span x-text="(page - 1) * take + 1"></span>
      to <span x-text="Math.min(page * take, count)"></span>
      of <span x-text="count"></span>
      results
    </p>

    <template x-if="count">
      <div class="join shrink-0 grow-0">
        <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page--" :disabled="page == 1">
          «
        </button>
        <template x-for="item in pagination">
          <button class="btn join-item btn-xs sm:btn-sm" :disabled="!item" :class="page == item && 'btn-active'"
            x-on:click="page = item" x-text="item || '..'">
          </button>
        </template>
        <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page++" :disabled="page == Math.ceil(count / take)">
          »
        </button>
      </div>
    </template>
  </div>

  <div x-show="deleteModal">
    <div class="modal" :class="deleteModal && 'modal-open'">
      <div class="card-compact card modal-box p-2" x-on:click.outside="!deleteLoading ? deleteModal = false : null">
        <div class="card-body">
          <h3 class="card-title text-lg font-bold">
            <x-gmdi-delete-r class="size-6 text-error" />
            Hapus Data <span x-text="deleteId"></span>
          </h3>
          <p>Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.</p>
          <div class="card-actions flex-row-reverse justify-start">
            <button class="btn btn-error btn-sm" x-on:click="deleteData()" :disabled="deleteLoading">
              Hapus
              <span class="loading loading-spinner loading-sm" x-show="deleteLoading"></span>
            </button>
            <button class="btn btn-neutral btn-sm" x-on:click="deleteModal = false" :disabled="deleteLoading">
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script title="alpine.js">
  function table_{{ $name }}() {
    return {
      data: [],
      loading: false,
      error: false,
      count: 0,
      page: 1,
      take: 10,
      order: null,
      direction: 'desc',
      search: '',
      deleteModal: false,
      deleteId: false,
      deleteLoading: false,
      _token: $('meta[name="csrf-token"]').attr('content'),

      get pagination() {
        const maxPage = Math.ceil(this.count / this.take);
        let pages = [];

        if (maxPage <= 6)
          pages = Array.from({
            length: maxPage
          }, (_, i) => i + 1);
        else if (this.page <= 4)
          pages = [1, 2, 3, 4, 5, null, maxPage];
        else if (this.page >= 5 && this.page <= maxPage - 4)
          pages = [1, null, this.page - 1, this.page, this.page + 1, null, maxPage];
        else
          pages = [1, null, maxPage - 4, maxPage - 3, maxPage - 2, maxPage - 1, maxPage];

        return pages;
      },

      getData() {
        this.error = false;
        this.loading = true;
        $.ajax({
          url: "{{ $routeList }}",
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            _token: this._token,
            take: this.take,
            page: this.page,
            order: this.order,
            direction: this.direction,
            search: this.search,
          }),
          success: (data) => {
            this.data = data;
          },
          error: (error) => {
            this.error = true;
          },
          complete: () => {
            this.loading = false;
          }
        })
      },

      sortData(order) {
        this.page = 1;
        if (this.order == order) {
          this.direction = this.direction == 'asc' ? 'desc' : 'asc';
        } else {
          this.order = order;
          this.direction = 'asc';
        }
        this.getData();
      },

      updateData(id) {
        const $el = $(this.$el)
        const isChecked = $el.is(':checked');
        const name = $el.data('name');
        $.ajax({
          url: `{{ $routeAction }}/${id}`,
          method: "PUT",
          contentType: 'application/json',
          data: JSON.stringify({
            _token: this._token,
            [name]: isChecked,
          }),
          success: (data) => {
            Alpine.store('toast').push({
              type: data.toast.type,
              title: data.toast.title,
              message: data.toast.message,
            })
          },
          error: (error) => {
            this.error = true;
          },
          complete: () => {
            this.loading = false;
          }
        })
      },

      deleteAction(id) {
        this.deleteId = id;
        this.deleteModal = true;
      },

      deleteData() {
        this.deleteLoading = true;
        $.ajax({
          url: `{{ $routeAction }}/${this.deleteId}`,
          method: 'DELETE',
          contentType: 'application/json',
          data: JSON.stringify({
            _token: this._token,
          }),
          complete: () => {
            window.location.reload()
          }
        })
      },

      init() {
        this.getData();
        this.$watch('page', () => {
          this.getData();
        });
        this.$watch('search', () => {
          if (this.page == 1) this.getData()
          else this.page = 1;
        });
        this.$watch('take', () => {
          if (this.page == 1) this.getData();
          else this.page = 1;
        });
      },
    }
  }
</script>
