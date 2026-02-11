<x-app-layout>
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-950 to-rose-950"></div>
        <div class="absolute right-0 top-0 h-72 w-72 -translate-y-24 translate-x-24 rounded-full bg-rose-500/30 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-72 w-72 translate-y-24 -translate-x-24 rounded-full bg-amber-400/20 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 sm:py-24 lg:grid-cols-2 lg:items-center lg:px-8">
            <div class="space-y-6">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-rose-300">Cinema booking</p>
                <h1 class="text-4xl font-semibold sm:text-5xl lg:text-6xl">
                    Feel every story <span class="text-rose-400">on the big screen</span>.
                </h1>
                <p class="text-base text-slate-300 sm:text-lg">
                    Discover premium cinema experiences, curated movie lineups, and seamless bookings with a bold, immersive interface.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ url('/movies') }}" class="btn-primary">Browse Movies</a>
                    @guest
                        <a href="{{ route('auth.login') }}" class="btn-ghost">Login</a>
                    @endguest
                </div>

            </div>

        </div>
    </section>

    <section class="bg-slate-950 py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Now Showing</p>
                    <h2 class="text-3xl font-semibold text-white">Top picks this week</h2>
                </div>
                <div class="flex flex-col gap-3 text-right sm:items-end">
                    <a href="{{ url('/movies') }}" class="text-sm text-rose-300 hover:text-rose-200">View full lineup →</a>
                </div>
            </div>

            @if ($nowShowing->isEmpty())
                <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                    No movies are showing right now. Check back soon.
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($nowShowing as $movie)
                        <x-movie-card
                            :title="$movie->title"
                            :start-date="optional($movie->show_start_date)->format('M d, Y')"
                            :end-date="optional($movie->show_end_date)->format('M d, Y')"
                            :times="$movie->show_times ?? []"
                            :image="$movie->cover_image_url"
                            tag="Now Showing"
                            cta-label="Book Now"
                            cta-href="{{ route('bookings.flow', $movie) }}"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="bg-slate-950 pb-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Upcoming</p>
                    <h2 class="text-3xl font-semibold text-white">Coming soon</h2>
                </div>
                <div class="flex flex-col gap-3 text-right sm:items-end">
                    <a href="{{ url('/movies') }}" class="text-sm text-rose-300 hover:text-rose-200">View full lineup →</a>
                </div>
            </div>

            @if ($upcoming->isEmpty())
                <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                    No upcoming movies yet. Check back soon.
                </div>
            @else
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($upcoming as $movie)
                        <x-movie-card
                            :title="$movie->title"
                            :start-date="optional($movie->show_start_date)->format('M d, Y')"
                            :end-date="optional($movie->show_end_date)->format('M d, Y')"
                            :times="$movie->show_times ?? []"
                            :image="$movie->cover_image_url"
                            tag="Upcoming"
                            cta-label="View Details"
                            cta-href="{{ route('movies.index') }}"
                        />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
