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
                        <a href="{{ route('login') }}" class="btn-ghost">Login</a>
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
                <div class="card-surface group overflow-hidden">
                    <div class="aspect-[3/4] bg-gradient-to-br from-slate-800 via-slate-900 to-black"></div>
                    <div class="p-4">
                        <p class="text-xs uppercase text-slate-400">Premiere</p>
                        <p class="text-lg font-semibold text-white">Starlight Run</p>
                        <p class="text-sm text-slate-400">Action · 2h 14m</p>
                    </div>
                </div>
                <div class="card-surface group overflow-hidden sm:mt-10">
                    <div class="aspect-[3/4] bg-gradient-to-br from-rose-900 via-slate-900 to-black"></div>
                    <div class="p-4">
                        <p class="text-xs uppercase text-slate-400">Exclusive</p>
                        <p class="text-lg font-semibold text-white">Velvet Horizon</p>
                        <p class="text-sm text-slate-400">Drama · 1h 58m</p>
                    </div>
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
                <a href="{{ url('/movies') }}" class="text-sm text-rose-300 hover:text-rose-200">View full lineup →</a>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Neon Drift', 'genre' => 'Sci-Fi · 2h 08m'],
                    ['title' => 'Crimson Echo', 'genre' => 'Thriller · 1h 52m'],
                    ['title' => 'Golden Hour', 'genre' => 'Romance · 2h 01m'],
                    ['title' => 'Atlas Rising', 'genre' => 'Adventure · 2h 20m'],
                ] as $movie)
                    <div class="group card-surface overflow-hidden transition hover:-translate-y-2 hover:border-white/20 hover:shadow-2xl hover:shadow-black/60">
                        <div class="relative aspect-[2/3] bg-gradient-to-br from-slate-800 via-slate-900 to-black">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-0 transition group-hover:opacity-100"></div>
                            <div class="absolute bottom-4 left-4 rounded-full bg-white/10 px-3 py-1 text-xs uppercase tracking-[0.2em] text-white">Book</div>
                        </div>
                        <div class="p-4">
                            <p class="text-lg font-semibold text-white">{{ $movie['title'] }}</p>
                            <p class="text-sm text-slate-400">{{ $movie['genre'] }}</p>
                        </div>
                    </div>
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
