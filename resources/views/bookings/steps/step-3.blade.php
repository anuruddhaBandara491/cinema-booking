<!-- Step 4 -->
<div x-show="step === 4" x-cloak x-transition.opacity.duration.300>
    @php
        $odcRows = ['A', 'B', 'C', 'D', 'E', 'F'];
        $odcColsMap = collect($odcRows)
            ->mapWithKeys(fn ($row) => [$row => range(1, $row === 'F' ? 20 : 16)])
            ->all();
        $boxRows = ['G', 'H'];
        $boxLeft = range(1, 4);
        $boxRight = range(5, 8);
    @endphp
    <h4 class="text-lg font-semibold text-white">Select Seats</h4>
    <p class="mt-2 text-sm text-slate-400">Pick your preferred seats. Selected seats: <span class="text-white" x-text="seats.join(', ') || 'None'"></span></p>

    <!-- Selected Seats List -->
    <div x-show="seats.length > 0" class="mt-4 rounded-lg bg-slate-900/40 border border-slate-700/40 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">
            <template x-for="seat in seats" :key="seat">
                <div class="rounded bg-emerald-500/20 border border-emerald-500/50 px-3 py-2 text-center">
                    <span class="font-semibold text-emerald-100" x-text="seat"></span>
                </div>
            </template>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 text-xs">
        <div class="flex items-center gap-2">
            <div class="h-4 w-4 rounded border border-white/40 bg-white/90"></div>
            <span class="text-slate-400">Available</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-4 w-4 rounded border border-emerald-400 bg-emerald-400"></div>
            <span class="text-slate-400">Selected</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-4 w-4 rounded border border-yellow-400 bg-yellow-400"></div>
            <span class="text-slate-400">Locked</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-4 w-4 rounded border border-red-400 bg-red-400"></div>
            <span class="text-slate-400">Booked</span>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-white/10 bg-canvas-muted p-3 sm:p-6">
        <div class="flex items-center justify-between text-[9px] sm:text-[11px] uppercase tracking-[0.2em] sm:tracking-[0.3em] text-slate-500 px-2 sm:px-0">
            <span>Entrance</span>
            <span class="hidden sm:inline">Theater Screen</span>
            <span class="sm:hidden">Screen</span>
            <span>Exit</span>
        </div>

        <div class="mt-2 sm:mt-3 flex items-center gap-2 sm:gap-4 px-2 sm:px-0">
            <div class="h-4 w-8 sm:h-6 sm:w-10 rounded-md bg-slate-700/60 flex-shrink-0"></div>
            <div class="h-2 sm:h-3 flex-1 rounded-full bg-gradient-to-r from-slate-500/40 via-slate-400/40 to-slate-500/40"></div>
            <div class="h-4 w-8 sm:h-6 sm:w-10 rounded-md bg-slate-700/60 flex-shrink-0"></div>
        </div>

        <!-- ODC Seats - Horizontal Scroll on Mobile -->
        <div class="mt-4 sm:mt-6 overflow-x-auto">
            <div class="inline-block min-w-full px-2 sm:px-0">
                <div class="flex flex-col gap-1 sm:gap-2">
                    @foreach ($odcRows as $row)
                        @php($cols = $odcColsMap[$row] ?? [])
                        <div class="flex justify-center">
                            <div class="inline-flex gap-0.5 sm:gap-1">
                                @foreach ($cols as $col)
                                    @php($seat = $row.$col)
                                    <button type="button"
                                        class="h-5 w-5 sm:h-8 sm:w-10 rounded-sm border border-white/40 bg-white/90 text-[6px] sm:text-[10px] font-semibold text-slate-900 transition hover:border-emerald-400/70 flex items-center justify-center flex-shrink-0"
                                        :class="{
                                            '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': seats.includes('{{ $seat }}'),
                                            '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                            '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}')
                                        }"
                                        @click="toggleSeat('{{ $seat }}')"
                                        :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}'))">
                                        {{ $seat }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-3 sm:mt-4 flex items-center justify-center px-2 sm:px-0">
            <span class="h-1 w-12 sm:w-24 rounded-full bg-slate-500/40"></span>
        </div>

        <!-- Box Seats -->
        <div class="mt-3 sm:mt-5 px-2 sm:px-0">
            <p class="text-xs uppercase tracking-[0.2em] sm:tracking-[0.3em] text-slate-400">Box</p>
            <div class="mt-2 sm:mt-3 flex flex-col gap-1 sm:gap-2">
                @foreach ($boxRows as $row)
                    <div class="flex items-center justify-center gap-1 sm:gap-6">
                        <div class="inline-flex gap-0.5 sm:gap-2 flex-shrink-0">
                            @foreach ($boxLeft as $col)
                                @php($seat = $row.$col)
                                <button type="button"
                                    class="h-5 w-5 sm:h-8 sm:w-10 rounded-sm border border-white/40 bg-white/90 text-[6px] sm:text-[10px] font-semibold text-slate-900 transition hover:border-emerald-400/70 flex items-center justify-center flex-shrink-0"
                                    :class="{
                                        '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': seats.includes('{{ $seat }}'),
                                        '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                        '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}')
                                    }"
                                    @click="toggleSeat('{{ $seat }}')"
                                    :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}'))">
                                    {{ $seat }}
                                </button>
                            @endforeach
                        </div>

                        <div class="h-4 sm:h-10 w-1 sm:w-2 rounded-full bg-slate-500/40 flex-shrink-0"></div>

                        <div class="inline-flex gap-0.5 sm:gap-2 flex-shrink-0">
                            @foreach ($boxRight as $col)
                                @php($seat = $row.$col)
                                <button type="button"
                                    class="h-5 w-5 sm:h-8 sm:w-10 rounded-sm border border-white/40 bg-white/90 text-[6px] sm:text-[10px] font-semibold text-slate-900 transition hover:border-emerald-400/70 flex items-center justify-center flex-shrink-0"
                                    :class="{
                                        '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': seats.includes('{{ $seat }}'),
                                        '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                        '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}')
                                    }"
                                    @click="toggleSeat('{{ $seat }}')"
                                    :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !seats.includes('{{ $seat }}'))">
                                    {{ $seat }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
