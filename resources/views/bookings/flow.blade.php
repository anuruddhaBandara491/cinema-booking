@php
    $windowDays = max(1, (int) ($movie->booking_window_days ?? 3));
    $dates = collect(range(0, $windowDays - 1))
        ->map(fn ($offset) => now()->addDays($offset)->format('D, M d'))
        ->values()
        ->all();
    $times = array_values($movie->show_times ?? []);
    $defaultDate = $dates[0] ?? '';
    $defaultTime = $times[0] ?? '';
    $ticketTypes = $ticketTypes ?? collect();
    $ticketCounts = $ticketTypes
        ->mapWithKeys(fn ($type) => [$type->id => ['adult' => 0, 'child' => 0]])
        ->all();
    $boxTypeIds = $ticketTypes
        ->filter(fn ($type) => strtolower($type->name) === 'box')
        ->pluck('id')
        ->values()
        ->all();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Booking</p>
            <h2 class="text-2xl font-semibold text-white">Complete your reservation</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
        step: 1,
        maxStep: 4,
        selectedDate: @js($defaultDate),
        selectedTime: @js($defaultTime),
        tickets: @js($ticketCounts),
        boxTypeIds: @js($boxTypeIds),
        seats: [],
        paymentMethod: 'card',
        toggleSeat(seat) {
            if (this.seats.includes(seat)) {
                this.seats = this.seats.filter(s => s !== seat);
            } else {
                this.seats.push(seat);
            }
        },
        totalTickets() {
            return Object.values(this.tickets).reduce((sum, item) => {
                return sum + (item.adult || 0) + (item.child || 0);
            }, 0);
        },
        ticketStep(id) {
            return this.boxTypeIds.includes(id) ? 2 : 1;
        },
        ticketTotal(id) {
            const item = this.tickets[id] || { adult: 0, child: 0 };
            return (item.adult || 0) + (item.child || 0);
        },
        isBoxValid(id) {
            return this.ticketTotal(id) % 2 === 0;
        },
        incrementTicket(id, key) {
            const step = this.ticketStep(id);
            this.tickets[id][key] += step;
        },
        decrementTicket(id, key) {
            const step = this.ticketStep(id);
            this.tickets[id][key] = Math.max(0, this.tickets[id][key] - step);
        },
        canContinue() {
            if (this.step === 1) return this.selectedDate && this.selectedTime;
            if (this.step === 2) {
                const hasTickets = this.totalTickets() > 0;
                const boxValid = this.boxTypeIds.every((id) => this.isBoxValid(id));
                return hasTickets && boxValid;
            }
            if (this.step === 3) return this.seats.length > 0;
            return true;
        }
    }">
        <div class="card-surface p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Step <span x-text="step"></span> of <span x-text="maxStep"></span></p>
                    <h3 class="text-xl font-semibold text-white">Book your seats</h3>
                </div>
                <div class="w-full max-w-md">
                    <div class="flex items-center justify-between text-xs uppercase tracking-[0.2em] text-slate-400">
                        <span>Select Date & Time</span>
                        <span>Payment</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-canvas-muted">
                        <div class="h-2 rounded-full bg-gradient-to-r from-primary-600 via-accent to-amber-400 transition-all" :style="`width: ${((step - 1) / (maxStep - 1)) * 100}%`"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-2 text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">
                        <div :class="step >= 1 ? 'text-white' : ''">Date</div>
                        <div :class="step >= 2 ? 'text-white' : ''">Tickets</div>
                        <div :class="step >= 3 ? 'text-white' : ''">Seats</div>
                        <div :class="step >= 4 ? 'text-white' : ''">Payment</div>
                    </div>
                </div>
            </div>

            <div class="mt-10 space-y-8">
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

                <!-- Step 2 -->
                <div x-show="step === 2" x-transition.opacity.duration.300>
                    <h4 class="text-lg font-semibold text-white">Select Tickets</h4>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @forelse ($ticketTypes as $ticketType)
                            <div class="rounded-2xl border border-white/10 bg-canvas-muted px-5 py-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">{{ $ticketType->name }}</p>
                                    @if (strtolower($ticketType->name) === 'box')
                                        <p class="mt-2 text-xs uppercase tracking-[0.2em] text-amber-300">Box seats must be selected in pairs</p>
                                    @endif
                                </div>
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Adult</p>
                                            <p class="text-lg font-semibold text-white">LKR {{ number_format($ticketType->adult_price, 2) }}</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                                    @click="decrementTicket({{ $ticketType->id }}, 'adult')">-</button>
                                            <span class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].adult"></span>
                                            <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                                    @click="incrementTicket({{ $ticketType->id }}, 'adult')">+</button>
                                        </div>
                                    </div>

                                    @if ($ticketType->has_child)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Child</p>
                                                <p class="text-lg font-semibold text-white">LKR {{ number_format($ticketType->child_price, 2) }}</p>
                                                @if (strtolower($ticketType->name) === 'odc')
                                                    <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-slate-500">Child seats allowed ages 2-13 only</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                                        @click="decrementTicket({{ $ticketType->id }}, 'child')">-</button>
                                                <span class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].child"></span>
                                                <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                                        @click="incrementTicket({{ $ticketType->id }}, 'child')">+</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full rounded-2xl border border-white/10 bg-canvas-muted p-6 text-center text-sm text-slate-400">
                                No ticket types configured yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Step 3 -->
                <div x-show="step === 3" x-transition.opacity.duration.300>
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

                    <div class="mt-6 rounded-3xl border border-white/10 bg-canvas-muted p-6">
                        <div class="flex items-center justify-between text-[11px] uppercase tracking-[0.3em] text-slate-500">
                            <span>Entrance</span>
                            <span>Theater Screen</span>
                            <span>Exit</span>
                        </div>

                        <div class="mt-3 flex items-center gap-4">
                            <div class="h-6 w-10 rounded-md bg-slate-700/60"></div>
                            <div class="h-3 flex-1 rounded-full bg-gradient-to-r from-slate-500/40 via-slate-400/40 to-slate-500/40"></div>
                            <div class="h-6 w-10 rounded-md bg-slate-700/60"></div>
                        </div>

                        <div class="mt-6 grid gap-3">
                            @foreach ($odcRows as $row)
                                @php($cols = $odcColsMap[$row] ?? [])
                                <div class="flex justify-center">
                                    <div class="inline-grid gap-2" style="grid-template-columns: repeat({{ count($cols) }}, minmax(0, 1fr));">
                                        @foreach ($cols as $col)
                                            @php($seat = $row.$col)
                                            <button type="button"
                                                class="h-8 w-10 rounded-md border border-rose-500/30 bg-rose-500/10 text-[10px] font-semibold text-rose-100 transition hover:border-rose-400/70"
                                                :class="seats.includes('{{ $seat }}') ? 'border-rose-300 bg-rose-400/30 text-white shadow-[0_0_12px_rgba(244,63,94,0.35)]' : ''"
                                                @click="toggleSeat('{{ $seat }}')">
                                                {{ $seat }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center justify-center">
                            <span class="h-1 w-24 rounded-full bg-slate-500/40"></span>
                        </div>

                        <div class="mt-5">
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Box</p>
                            <div class="mt-3 grid gap-3">
                                @foreach ($boxRows as $row)
                                    <div class="flex items-center justify-center gap-6">
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach ($boxLeft as $col)
                                                @php($seat = $row.$col)
                                                <button type="button"
                                                    class="rounded-md border border-rose-500/30 bg-rose-500/10 px-2 py-1.5 text-[10px] font-semibold text-rose-100 transition hover:border-rose-400/70"
                                                    :class="seats.includes('{{ $seat }}') ? 'border-rose-300 bg-rose-400/30 text-white shadow-[0_0_12px_rgba(244,63,94,0.35)]' : ''"
                                                    @click="toggleSeat('{{ $seat }}')">
                                                    {{ $seat }}
                                                </button>
                                            @endforeach
                                        </div>

                                        <div class="h-10 w-2 rounded-full bg-slate-500/40"></div>

                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach ($boxRight as $col)
                                                @php($seat = $row.$col)
                                                <button type="button"
                                                    class="rounded-md border border-rose-500/30 bg-rose-500/10 px-2 py-1.5 text-[10px] font-semibold text-rose-100 transition hover:border-rose-400/70"
                                                    :class="seats.includes('{{ $seat }}') ? 'border-rose-300 bg-rose-400/30 text-white shadow-[0_0_12px_rgba(244,63,94,0.35)]' : ''"
                                                    @click="toggleSeat('{{ $seat }}')">
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

                <!-- Step 4 -->
                <div x-show="step === 4" x-transition.opacity.duration.300>
                    <h4 class="text-lg font-semibold text-white">Payment</h4>
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Summary</p>
                            <div class="mt-3 space-y-2 text-sm text-slate-300">
                                <p><span class="text-white">Date:</span> <span x-text="selectedDate"></span></p>
                                <p><span class="text-white">Time:</span> <span x-text="selectedTime"></span></p>
                                <p><span class="text-white">Tickets:</span> <span x-text="totalTickets()"></span></p>
                                <p><span class="text-white">Seats:</span> <span x-text="seats.join(', ')"></span></p>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Payment Method</p>
                            <div class="mt-3 space-y-3">
                                <label class="flex items-center gap-3 text-sm text-white">
                                    <input type="radio" name="payment" value="card" class="h-4 w-4" checked @click="paymentMethod = 'card'" />
                                    Card payment
                                </label>
                                <label class="flex items-center gap-3 text-sm text-white">
                                    <input type="radio" name="payment" value="wallet" class="h-4 w-4" @click="paymentMethod = 'wallet'" />
                                    Wallet
                                </label>
                                <label class="flex items-center gap-3 text-sm text-white">
                                    <input type="radio" name="payment" value="cash" class="h-4 w-4" @click="paymentMethod = 'cash'" />
                                    Pay at counter
                                </label>
                            </div>
                            <button type="button" class="btn-primary mt-6 w-full">Confirm payment</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" class="btn-ghost" @click="step = Math.max(1, step - 1)" :disabled="step === 1">Back</button>
                <div class="flex items-center gap-3">
                    <button type="button" class="btn-ghost" @click="step = Math.min(maxStep, step + 1)" :disabled="!canContinue() || step === maxStep">Next</button>
                    <a href="{{ url('/movies') }}" class="text-xs uppercase tracking-[0.2em] text-slate-400 hover:text-white">Cancel</a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
