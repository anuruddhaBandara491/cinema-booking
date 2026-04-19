<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Dashboard</p>
            <h2 class="text-2xl font-semibold text-white">Welcome back, {{ auth()->user()->name }}</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card-surface p-6 lg:col-span-2">
                <h3 class="text-xl font-semibold text-white">Upcoming bookings</h3>
                <p class="text-sm text-slate-400">Your next reservations, all in one place.</p>

                <div class="mt-6 space-y-4">
                    @foreach ([
                        ['title' => 'Neon Drift', 'time' => 'Fri · 7:30 PM', 'screen' => 'Screen 4'],
                        ['title' => 'Golden Hour', 'time' => 'Sat · 9:10 PM', 'screen' => 'Screen 2'],
                    ] as $booking)
                        <div class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-slate-900/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-lg font-semibold text-white">{{ $booking['title'] }}</p>
                                <p class="text-sm text-slate-400">{{ $booking['time'] }} · {{ $booking['screen'] }}</p>
                            </div>
                            <button class="btn-ghost">View Ticket</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-surface p-6">
                <h3 class="text-lg font-semibold text-white">Quick actions</h3>
                <div class="mt-4 space-y-3">
                    <a href="{{ url('/movies') }}" class="btn-primary w-full">Browse Movies</a>
                    <a href="{{ url('/bookings') }}" class="btn-ghost w-full">My Bookings</a>
                </div>
                <div class="mt-6 rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900 via-slate-900 to-rose-950 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-rose-300">Member</p>
                    <p class="text-lg font-semibold text-white">Priority access</p>
                    <p class="text-sm text-slate-400">Unlock early bookings and premium lounges.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
