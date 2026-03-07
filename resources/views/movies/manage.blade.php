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
            category_id: '',
            language_id: '',
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
                    category_id: payload.movie.category_id || '',
                    language_id: payload.movie.language_id || '',
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
                    category_id: '',
                    language_id: '',
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
                        'category_id' => $movie->category_id,
                        'language_id' => $movie->language_id,
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
            <div class="bg-slate-900 p-8 rounded-2xl">

                {{-- Header --}}
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500 mb-1"
                        x-text="mode === 'create' ? 'Add movie' : 'Edit movie'"></p>
                        <h3 class="text-2xl font-semibold text-white"
                            x-text="mode === 'create' ? 'Create a new listing' : 'Update movie details'"></h3>
                    </div>
                    <button
                        class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-slate-400 transition hover:bg-slate-700 hover:text-white"
                        x-on:click="$dispatch('close-modal', 'movie-form')">
                        ✕
                    </button>
                </div>

                <form class="space-y-5" method="POST" :action="formAction" enctype="multipart/form-data">
                    @csrf
                    <template x-if="method === 'put'">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>
                    <input type="hidden" name="publish_immediately" :value="form.status === 'published' ? 1 : 0" />
                    <input type="hidden" name="is_upcoming" :value="form.status === 'upcoming' ? 1 : 0" />

                    {{-- Film Name --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Film Name</label>
                        <input type="text" name="title" x-model="form.title" required
                            placeholder="Enter film title"
                            class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-600 transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                    </div>

                    {{-- Category & Language --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Category</label>
                            <select name="category_id" x-model="form.category_id" required
                                    class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30 appearance-none">
                                <option value="" class="text-slate-500">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Language</label>
                            <select name="language_id" x-model="form.language_id" required
                                    class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30 appearance-none">
                                <option value="" class="text-slate-500">Select Language</option>
                                @foreach ($languages as $language)
                                    <option value="{{ $language->id }}">{{ $language->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Start Date & End Date --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Start Date</label>
                            <input type="date" name="show_start_date" x-model="form.show_start_date" required
                                class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">End Date</label>
                            <input type="date" name="show_end_date" x-model="form.show_end_date" required
                                class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                        </div>
                    </div>

                    {{-- Show Times --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Show Times <span class="normal-case tracking-normal text-slate-500">(comma separated)</span></label>
                        <input type="text" name="show_times" x-model="form.show_times"
                            placeholder="e.g. 2:00 PM, 6:30 PM, 10:00 PM" required
                            class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-600 transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                    </div>

                    {{-- Booking Window, Booking Charge, Cover Image --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Booking Window <span class="normal-case tracking-normal text-slate-500">(days)</span></label>
                            <input type="number" min="1" max="14" step="1" name="booking_window_days"
                                x-model.number="form.booking_window_days" required
                                class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Booking Charge <span class="normal-case tracking-normal text-slate-500">(LKR)</span></label>
                            <input type="number" name="booking_charge" step="0.01" min="0"
                                x-model="form.booking_charge" required
                                class="w-full rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-white transition focus:border-rose-500/50 focus:outline-none focus:ring-1 focus:ring-rose-500/30" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-2">Cover Image</label>
                            <label class="flex w-full cursor-pointer items-center gap-2 rounded-xl border border-white/10 bg-slate-800 px-4 py-3 text-sm text-slate-400 transition hover:border-rose-500/40 hover:text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="truncate text-xs" id="file-label">Choose file…</span>
                                <input type="file" name="cover_image" accept="image/*" class="sr-only"
                                    onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Choose file…'" />
                            </label>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.25em] text-slate-400 mb-3">Status</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-xs transition"
                                :class="form.status === 'published'
                                    ? 'border-emerald-500/50 bg-emerald-500/10 text-emerald-300'
                                    : 'border-white/10 bg-slate-800 text-slate-400 hover:border-white/20 hover:text-slate-300'">
                                <input type="radio" name="status" value="published" x-model="form.status" class="sr-only" />
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition"
                                    :class="form.status === 'published' ? 'border-emerald-400 bg-emerald-400' : 'border-slate-600'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900" x-show="form.status === 'published'"></span>
                                </span>
                                Now Showing
                            </label>
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-xs transition"
                                :class="form.status === 'upcoming'
                                    ? 'border-amber-400/50 bg-amber-400/10 text-amber-300'
                                    : 'border-white/10 bg-slate-800 text-slate-400 hover:border-white/20 hover:text-slate-300'">
                                <input type="radio" name="status" value="upcoming" x-model="form.status" class="sr-only" />
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition"
                                    :class="form.status === 'upcoming' ? 'border-amber-400 bg-amber-400' : 'border-slate-600'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900" x-show="form.status === 'upcoming'"></span>
                                </span>
                                Upcoming
                            </label>
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-xs transition"
                                :class="form.status === 'draft'
                                    ? 'border-slate-400/40 bg-slate-400/10 text-slate-300'
                                    : 'border-white/10 bg-slate-800 text-slate-400 hover:border-white/20 hover:text-slate-300'">
                                <input type="radio" name="status" value="draft" x-model="form.status" class="sr-only" />
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition"
                                    :class="form.status === 'draft' ? 'border-slate-400 bg-slate-400' : 'border-slate-600'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900" x-show="form.status === 'draft'"></span>
                                </span>
                                Draft
                            </label>
                        </div>
                    </div>

                    {{-- Enable Book Now --}}
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-slate-800 px-4 py-3 transition hover:border-white/20"
                        :class="form.book_now ? 'border-rose-500/30 bg-rose-500/5' : ''">
                        <span class="relative flex h-5 w-5 shrink-0 items-center justify-center rounded border-2 transition"
                            :class="form.book_now ? 'border-rose-500 bg-rose-500' : 'border-slate-600 bg-slate-700'">
                            <svg x-show="form.book_now" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <input type="checkbox" name="book_now" x-model="form.book_now" class="sr-only" />
                        </span>
                        <span class="text-xs uppercase tracking-[0.25em]"
                            :class="form.book_now ? 'text-slate-300' : 'text-slate-400'">
                            Enable Book Now Button
                        </span>
                    </label>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'movie-form')">Cancel</button>
                        <button class="btn-primary" type="submit"
                                x-text="mode === 'create' ? 'Save Movie' : 'Save Changes'"></button>
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
