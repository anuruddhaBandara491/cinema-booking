<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Movies</p>
                <h2 class="text-2xl font-semibold text-white">Manage movie lineup</h2>
            </div>
            @hasanyrole('manager|admin')
                <button type="button" class="btn-primary" onclick="window.dispatchEvent(new CustomEvent('movie-form:open', { detail: { mode: 'create' } }))">
                    Add Movie
                </button>
            @endhasanyrole
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
        mode: 'create',
        formAction: '{{ route('manager.movies.store') }}',
        method: 'post',
        form: {
            id: null,
            title: '',
            show_start_date: '',
            show_end_date: '',
            show_times: '',
            booking_charge: 35,
            booking_window_days: 3,
            status: 'draft',
            book_now: true,
        },
        openForm(payload) {
            this.mode = payload.mode || 'create';
            if (this.mode === 'edit') {
                const status = payload.movie.is_published
                    ? 'published'
                    : (payload.movie.is_upcoming ? 'upcoming' : 'draft');
                this.form = {
                    id: payload.movie.id,
                    title: payload.movie.title,
                    show_start_date: payload.movie.show_start_date,
                    show_end_date: payload.movie.show_end_date,
                    show_times: (payload.movie.show_times || []).join(', '),
                    booking_charge: payload.movie.booking_charge,
                    booking_window_days: payload.movie.booking_window_days ?? 3,
                    status,
                    book_now: payload.movie.book_now ?? false,
                };
                this.formAction = payload.movie.update_url;
                this.method = 'put';
            } else {
                this.form = {
                    id: null,
                    title: '',
                    show_start_date: '',
                    show_end_date: '',
                    show_times: '',
                    booking_charge: 35,
                    booking_window_days: 3,
                    status: 'draft',
                    book_now: true,
                };
                this.formAction = '{{ route('manager.movies.store') }}';
                this.method = 'post';
            }
            $dispatch('open-modal', 'movie-form');
        },
        openDelete(payload) {
            this.form = { id: payload.movie.id, title: payload.movie.title };
            this.formAction = payload.movie.delete_url;
            $dispatch('open-modal', 'movie-delete');
        }
    }" x-on:movie-form:open.window="openForm($event.detail)">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($movies as $movie)
                @php
                    $moviePayload = [
                        'id' => $movie->id,
                        'title' => $movie->title,
                        'show_start_date' => optional($movie->show_start_date)->toDateString(),
                        'show_end_date' => optional($movie->show_end_date)->toDateString(),
                        'show_times' => $movie->show_times ?? [],
                        'booking_charge' => $movie->booking_charge,
                        'booking_window_days' => $movie->booking_window_days ?? 3,
                        'is_published' => $movie->is_published,
                        'is_upcoming' => $movie->is_upcoming,
                        'update_url' => route('manager.movies.update', $movie),
                        'delete_url' => route('manager.movies.destroy', $movie),
                    ];
                    $moviePayloadJson = json_encode(
                        $moviePayload,
                        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                    );
                @endphp
                <div x-data="{ movie: JSON.parse($el.dataset.movie) }" data-movie='{{ $moviePayloadJson }}'>
                    <x-movie-card
                        :title="$movie->title"
                        :start-date="optional($movie->show_start_date)->format('M d, Y')"
                        :end-date="optional($movie->show_end_date)->format('M d, Y')"
                        :times="$movie->show_times ?? []"
                        :tag="$movie->is_published ? 'Published' : ($movie->is_upcoming ? 'Upcoming' : 'Draft')"
                        :image="$movie->cover_image_url"
                        :book-now="$movie->book_now"
                        cta-label="Book Now"
                        cta-href="{{ route('bookings.flow', $movie) }}"
                        edit-action="openForm({ mode: 'edit', movie })"
                        delete-action="openDelete({ movie })"
                    />
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                    No movies yet. Add your first listing.
                </div>
            @endforelse
        </div>

        <x-modal name="movie-form" maxWidth="2xl" focusable>
            <div class="bg-slate-950 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400" x-text="mode === 'create' ? 'Add movie' : 'Edit movie'"></p>
                        <h3 class="text-xl font-semibold text-white" x-text="mode === 'create' ? 'Create a new listing' : 'Update movie details'"></h3>
                    </div>
                    <button class="text-slate-400 hover:text-white" x-on:click="$dispatch('close-modal', 'movie-form')">✕</button>
                </div>

                <form class="mt-6 space-y-4" method="POST" :action="formAction" enctype="multipart/form-data">
                    @csrf
                    <template x-if="method === 'put'">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>
                    <input type="hidden" name="publish_immediately" :value="form.status === 'published' ? 1 : 0" />
                    <input type="hidden" name="is_upcoming" :value="form.status === 'upcoming' ? 1 : 0" />

                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Film Name</label>
                        <input type="text" name="title" x-model="form.title" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Start Date</label>
                            <input type="date" name="show_start_date" x-model="form.show_start_date" required
                                   class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-400">End Date</label>
                            <input type="date" name="show_end_date" x-model="form.show_end_date" required
                                   class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Show Times (comma separated)</label>
                        <input type="text" name="show_times" x-model="form.show_times" placeholder="2:00 PM, 10:00 PM" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>

                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Booking Window (days)</label>
                        <input type="number" min="1" max="14" step="1" name="booking_window_days" x-model.number="form.booking_window_days" required
                               class="mt-2 w-28 rounded-xl border border-white/10 bg-canvas-muted px-3 py-2 text-xs text-white" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Booking Charge (LKR)</label>
                            <input type="number" name="booking_charge" step="0.01" min="0" x-model="form.booking_charge" required
                                   class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                        </div>
                        <div>
                            <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Cover Image</label>
                            <input type="file" name="cover_image" accept="image/*"
                                   class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-xs text-white" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Status</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-xs text-slate-300">
                                <input type="radio" name="status" value="published" x-model="form.status"
                                       class="h-4 w-4 rounded-full border-white/20 bg-slate-900 text-rose-400" />
                                Now Showing
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-xs text-slate-300">
                                <input type="radio" name="status" value="upcoming" x-model="form.status"
                                       class="h-4 w-4 rounded-full border-white/20 bg-slate-900 text-amber-300" />
                                Upcoming
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-xs text-slate-300">
                                <input type="radio" name="status" value="draft" x-model="form.status"
                                       class="h-4 w-4 rounded-full border-white/20 bg-slate-900 text-slate-400" />
                                Draft
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" name="book_now" x-model="form.book_now" class="form-checkbox h-5 w-5 text-accent" />
                            <span class="ml-2 text-xs uppercase tracking-[0.2em] text-slate-400">Enable Book Now button</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'movie-form')">Cancel</button>
                        <button class="btn-primary" type="submit" x-text="mode === 'create' ? 'Save Movie' : 'Save Changes'"></button>
                    </div>
                </form>
            </div>
        </x-modal>

        <x-modal name="movie-delete" maxWidth="lg" focusable>
            <div class="bg-slate-950 p-6">
                <h3 class="text-xl font-semibold text-white">Delete movie</h3>
                <p class="mt-2 text-sm text-slate-400">Are you sure you want to remove <span class="text-white" x-text="form.title"></span>?</p>
                <form method="POST" :action="formAction" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('delete')
                    <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'movie-delete')">Cancel</button>
                    <button class="rounded-full bg-rose-600 px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-white">Delete</button>
                </form>
            </div>
        </x-modal>
    </section>
</x-app-layout>
