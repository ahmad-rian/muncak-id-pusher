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
        <div class="min-h-[40rem] max-w-screen-sm" x-data="ulasan">
          <div class="border-b border-base-300 font-merriweather">
            <span class="inline-block border-b-2 border-base-content px-4 pb-2 font-semibold">
              Ulasan Anda
            </span>
          </div>
          <div class="mt-4 flex flex-col justify-end gap-4 md:flex-row">
            <div class="flex basis-2/3 items-center gap-2">
              <p class="shrink-0 grow-0 text-sm font-medium">Urutkan berdasarkan</p>
              <div class="shrink grow">
                <select class="select select-bordered select-sm w-full" id="order" name="order"
                  x-on:change="handleOrdering">
                  <option value="newest">Terbaru</option>
                  <option value="oldest">Terlama</option>
                  <option value="highestRating">Rating Tertinggi</option>
                  <option value="lowestRating">Rating Terendah</option>
                </select>
              </div>
            </div>
          </div>

          <div id="auto-animate-1">
            <template x-if="!count && !error && !loading">
              <div class="alert mt-8" role="alert">
                <x-gmdi-chat-r class="size-6" />
                <div>
                  <p class="font-semibold">Belum Ada Ulasan</p>
                  <p>Anda belum menulis ulasan pada jalur pendakian manapun.</p>
                </div>
              </div>
            </template>

            <template x-if="error">
              <div class="alert mt-8" role="alert">
                <x-gmdi-warning-r class="size-8 shrink-0 text-error" />
                <div>
                  <h3 class="font-semibold">Error!</h3>
                  <div class="text-sm">Terjadi error saat mendapatkan ulasan jalur pendakian ini.</div>
                </div>
                <button class="btn btn-sm" x-on:click="getData">Refresh</button>
              </div>
            </template>

            <template x-if="count">
              <div>
                <div class="mt-8 space-y-8">
                  <template x-for="item in data">
                    <div>
                      <p class="font-medium">
                        <span>Anda memberikan ulasan di</span>
                        <a class="text-primary underline"
                          x-text="`Gunung ${item.rute.gunung.nama} via ${item.rute.nama}`" :href="item.rute.url"></a>
                        <span>pada</span>
                        <span x-text="item.created_at_id"></span>
                      </p>
                      <div class="mt-2 flex items-stretch gap-x-4">
                        <img
                          class="md:size-12 size-10 sticky top-20 shrink-0 grow-0 rounded-md object-cover object-center"
                          :src="item.user.avatar_url" alt="user-photo-profile" />
                        <div class="shrink grow">
                          <div class="flex justify-between">
                            <div class="shrink grow">
                              <p class="line-clamp-1 font-medium" x-text="item.user.name ?? item.user.username"></p>
                              <p class="line-clamp-1 text-sm text-base-content/70" x-text="`@${item.user.username}`">
                              </p>
                            </div>
                            <button class="btn btn-square btn-ghost btn-error btn-sm m-1 text-error" type="button"
                              x-on:click="openDeleteModal(item.id)">
                              <x-gmdi-delete-r class="size-4" />
                            </button>
                          </div>
                          <div class="mt-2 rounded-md bg-base-200 px-4 py-2 md:py-4">
                            <p x-text="item.content"></p>
                            <div class="mt-2 flex items-center">
                              <template x-for="index in 5" :key="index">
                                <span
                                  :class="{ 'text-yellow-500': index <= item.rating, 'text-gray-500': index > item.rating }">
                                  <x-gmdi-star class="md:size-5 size-4 shrink-0 grow-0" />
                                </span>
                              </template>
                            </div>
                          </div>
                          <template x-if="item.gallery_urls.length">
                            <div class="no-scrollbar mt-2 flex shrink-0 grow-0 gap-2 overflow-x-auto">
                              <template x-for="url in item.gallery_urls">
                                <img
                                  class="expandable-image md:size-16 size-14 shrink-0 grow-0 cursor-pointer rounded-md object-cover object-center"
                                  :src="url" alt="comment-gallery">
                              </template>
                            </div>
                          </template>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
                <div class="mt-4 flex justify-end">
                  <div class="join shrink-0 grow-0">
                    <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page--" :disabled="page == 1">
                      «
                    </button>
                    <template x-for="item in pagination">
                      <button class="btn join-item btn-xs sm:btn-sm" :disabled="!item"
                        :class="page == item && 'btn-active'" x-on:click="page = item" x-text="item || '..'">
                      </button>
                    </template>
                    <button class="btn join-item btn-xs sm:btn-sm" x-on:click="page++"
                      :disabled="page == Math.ceil(count / take)">
                      »
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <dialog class="modal" id="imageModal">
            <div class="modal-box max-w-screen-sm !p-0" x-data x-on:click.outside="imageModal.close()">
              <form class="absolute right-2 top-2 flex justify-end" method="dialog">
                <button class="btn btn-square btn-sm">
                  <x-gmdi-close-r />
                </button>
              </form>
              <img class="h-60 w-full object-cover object-center sm:h-72 md:h-80 lg:h-96" src="" alt="">
            </div>
          </dialog>

          <dialog class="modal" id="deleteModal">
            <div class="modal-box max-w-screen-sm p-2">
              <div class="card-compact card p-2">
                <div class="card-body">
                  <form :action="`/profile/ulasan/${deleteId}/delete`" method="post">
                    @csrf
                    @method('DELETE')
                    <h3 class="card-title text-lg font-bold">
                      <x-gmdi-delete-r class="size-6 text-error" />
                      Hapus Ulasan
                    </h3>
                    <p class="mt-2">Apakah Anda yakin ingin menghapus ulasan ini? Tindakan ini tidak dapat
                      dikembalikan.</p>
                    <div class="card-actions mt-4 flex-row-reverse justify-start">
                      <button class="btn btn-error btn-sm" type="submit">
                        Hapus
                      </button>
                      <button class="btn btn-neutral btn-sm" type="button" x-on:click="deleteModal.close()">
                        Batal
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </dialog>
        </div>
      </div>
    </div>
  </div>

  <x-slot:js>
    <script>
      function ulasan() {
        return {
          data: [],
          count: 0,
          rating: 0,
          loading: false,
          error: false,
          take: 20,
          page: 1,
          order: 'creation',
          direction: 'desc',

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

          init() {
            this.getData();
            this.$watch('page', () => this.getData())
          },

          getData() {
            this.loading = true;
            this.error = false;
            $.ajax({
              url: `{{ route('api.profile.ulasan') }}?take=${this.take}&page=${this.page}&order=${this.order}&direction=${this.direction}`,
              method: 'GET',
              dataType: 'json',
              success: (data) => {
                this.data = data.data;
                this.count = data.count;
                this.rating = data.rating;
                this.error = false;
              },
              error: (error) => {
                this.error = true;
              },
              complete: () => {
                this.loading = false;
              }
            });
          },

          handleOrdering() {
            const val = $(this.$el).val();
            if (val == 'newest') {
              this.order = 'creation';
              this.direction = 'desc';
            }
            if (val == 'oldest') {
              this.order = 'creation';
              this.direction = 'asc';
            }
            if (val == 'highestRating') {
              this.order = 'rating';
              this.direction = 'desc';
            }
            if (val == 'lowestRating') {
              this.order = 'rating';
              this.direction = 'asc';
            }
            this.getData();
          },

          deleteId: null,
          openDeleteModal(id) {
            this.deleteId = id;
            // console.log("Hello");
            deleteModal.showModal();
          }
        }
      }

      $(function() {
        $(document).on('click', '.expandable-image', function() {
          $('#imageModal img').attr('src', $(this).attr('src'));
          $('#imageModal img').attr('alt', $(this).attr('src'));
          imageModal.showModal();
        })
      })
    </script>

    <script type="module">
      import autoAnimate from "{{ asset('js/auto-animate.min.js') }}"
      $(function() {
        autoAnimate(document.getElementById('auto-animate-1'));
      })
    </script>
  </x-slot:js>
</x-layout.app>
