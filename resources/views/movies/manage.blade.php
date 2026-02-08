<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Movies</p>
                <h2 class="text-2xl font-semibold text-white">Manage movie lineup</h2>
            </div>
            @hasanyrole('manager|admin')
                <button type="button" class="btn-primary" @click="$dispatch('open-modal', 'movie-form'); $dispatch('movie-form:open', { mode: 'create' })">
                    Add Movie
                </button>
            @endhasanyrole
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
        movie: null,
        mode: 'create',
        openForm(payload) {
            this.mode = payload.mode || 'create';
            this.movie = payload.movie || { title: '', tag: '', times: [''] };
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'movie-form' }));
        },
        openDelete(payload) {
            this.movie = payload.movie;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'movie-delete' }));
        }
    }" x-on:movie-form:open.window="openForm($event.detail)">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['id' => 1, 'title' => 'Neon Drift', 'tag' => 'Now Showing', 'times' => ['5:40 PM', '8:20 PM']],
                ['id' => 2, 'title' => 'Crimson Echo', 'tag' => 'New', 'times' => ['6:10 PM', '9:05 PM']],
                ['id' => 3, 'title' => 'Golden Hour', 'tag' => 'Top Rated', 'times' => ['4:30 PM', '7:15 PM']],
                ['id' => 4, 'title' => 'Atlas Rising', 'tag' => 'Adventure', 'times' => ['7:00 PM', '9:50 PM']],
            ] as $movie)
                <x-movie-card
                    :title="$movie['title']"
                    :times="$movie['times']"
                    :tag="$movie['tag']"
                    cta-label="View"
                    cta-href="{{ route('bookings.flow') }}"
                    edit-action="openForm({ mode: 'edit', movie: @js($movie) })"
                    delete-action="openDelete({ movie: @js($movie) })"
                />
            @endforeach
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

                <form class="mt-6 space-y-4" x-on:submit.prevent="$dispatch('close-modal', 'movie-form')">
                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Title</label>
                        <input type="text" x-model="movie.title" class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Tag</label>
                        <input type="text" x-model="movie.tag" class="mt-2 w-full rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-white" />
                    </div>
                    <div>
                        <label class="text-xs uppercase tracking-[0.2em] text-slate-400">Show Times</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="(time, index) in movie.times" :key="index">
                                <input type="text" x-model="movie.times[index]" class="w-28 rounded-full border border-white/10 bg-canvas-muted px-3 py-2 text-xs text-white" />
                            </template>
                            <button type="button" class="rounded-full border border-white/10 px-3 py-2 text-xs text-white" @click="movie.times.push('')">+ Add</button>
                        </div>
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
                <p class="mt-2 text-sm text-slate-400">Are you sure you want to remove <span class="text-white" x-text="movie?.title"></span>?</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="btn-ghost" x-on:click="$dispatch('close-modal', 'movie-delete')">Cancel</button>
                    <button class="rounded-full bg-rose-600 px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-white" x-on:click="$dispatch('close-modal', 'movie-delete')">Delete</button>
                </div>
            </div>
        </x-modal>
    </section>
</x-app-layout>
