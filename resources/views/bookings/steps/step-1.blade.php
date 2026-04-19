<!-- Step 1: Date & Time Selection -->
<div x-show="step === 1" x-cloak x-transition.opacity.duration.300>
    @if ($isExpired)
        <!-- Show Expired Message -->
        <div class="flex items-center justify-center rounded-2xl border border-rose-500/30 bg-gradient-to-br from-rose-500/10 to-rose-600/5 p-12">
            <div class="text-center">
                <svg class="mx-auto mb-4 h-16 w-16 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">Show Period Ended</h3>
                <p class="text-slate-300 mb-4">This movie's booking period has ended.</p>
                <p class="text-sm text-slate-400">
                    <span class="font-medium">Show Period:</span> {{ $movie->show_start_date->format('M d, Y') }} to {{ $movie->show_end_date->format('M d, Y') }}
                </p>
                <a href="{{ route('dashboard') }}" class="mt-6 inline-block rounded-lg bg-rose-500 px-6 py-2 font-medium text-white hover:bg-rose-600 transition">
                    Back to Home
                </a>
            </div>
        </div>
    @else
        <!-- Date & Time Selection -->
        <div class="grid gap-6 lg:grid-cols-2">
            <div>
                <h4 class="text-lg font-semibold text-white">Select Date</h4>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($dates as $date)
                        <button type="button"
                            class="rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-slate-200 transition hover:border-rose-400/60"
                            :class="selectedDate === '{{ $date }}' ? 'border-rose-400/80 bg-gradient-to-br from-rose-500/30 via-rose-500/10 to-transparent text-white shadow-[0_0_20px_rgba(244,63,94,0.35)] ring-1 ring-rose-400/40' : ''"
                            @click="selectedDate = '{{ $date }}'">
                            {{ $date }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-white">Select Time</h4>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach ($times as $time)
                        <button type="button"
                            class="rounded-full border border-white/10 bg-canvas-muted px-4 py-2 text-sm text-slate-200 transition hover:border-amber-300/60"
                            :class="selectedTime === '{{ $time }}' ? 'border-amber-300/80 bg-gradient-to-br from-amber-300/30 via-amber-300/10 to-transparent text-white shadow-[0_0_16px_rgba(251,191,36,0.35)] ring-1 ring-amber-300/40' : ''"
                            @click="selectedTime = '{{ $time }}'">
                            {{ $time }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
