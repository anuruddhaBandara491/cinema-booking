<!-- Step 1 -->
<div x-show="step === 1" x-transition.opacity.duration.300>
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
</div>
