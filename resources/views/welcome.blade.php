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
                <div class="grid grid-cols-2 gap-4 pt-6 sm:grid-cols-3">
                    <div class="card-surface px-4 py-3">
                        <p class="text-xs uppercase text-slate-400">Screens</p>
                        <p class="text-2xl font-semibold text-white">12</p>
                    </div>
                    <div class="card-surface px-4 py-3">
                        <p class="text-xs uppercase text-slate-400">Now Showing</p>
                        <p class="text-2xl font-semibold text-white">28</p>
                    </div>
                    <div class="card-surface px-4 py-3">
                        <p class="text-xs uppercase text-slate-400">Next 7 Days</p>
                        <p class="text-2xl font-semibold text-white">64</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <x-movie-card
                    title="Starlight Run"
                    tag="Premiere"
                    :times="['7:30 PM', '9:45 PM']"
                    cta-label="Book Now"
                    cta-href="{{ route('bookings.flow') }}"
                />
                <x-movie-card
                    title="Velvet Horizon"
                    tag="Exclusive"
                    :times="['6:15 PM', '8:40 PM']"
                    cta-label="Book Now"
                    cta-href="{{ route('bookings.flow') }}"
                    class="sm:mt-10"
                />
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
                    @hasanyrole('manager|admin')
                        <a href="{{ url('/manager/movies/create') }}" class="btn-primary">Add Movie</a>
                    @endhasanyrole
                </div>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Neon Drift', 'times' => ['5:40 PM', '8:20 PM']],
                    ['title' => 'Crimson Echo', 'times' => ['6:10 PM', '9:05 PM']],
                    ['title' => 'Golden Hour', 'times' => ['4:30 PM', '7:15 PM']],
                    ['title' => 'Atlas Rising', 'times' => ['7:00 PM', '9:50 PM']],
                ] as $movie)
                    <x-movie-card
                        :title="$movie['title']"
                        :times="$movie['times']"
                        cta-label="Book Now"
                        cta-href="{{ route('bookings.flow') }}"
                        edit-label="Edit Movie"
                        edit-href="{{ url('/manager/movies') }}"
                    />
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-slate-950 pb-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="card-surface grid gap-8 p-8 lg:grid-cols-3 lg:items-center">
                <div class="lg:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Coming Soon</p>
                    <h3 class="text-2xl font-semibold text-white">Reserve seats for the most anticipated releases.</h3>
                    <p class="mt-2 text-sm text-slate-400">Stay ahead with early alerts, premium seating, and VIP lounge access.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                    <button class="btn-primary w-full">Get Alerts</button>
                    <button class="btn-ghost w-full">See Schedule</button>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
