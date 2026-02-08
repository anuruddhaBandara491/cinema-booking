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
        selectedDate: 'Fri, Feb 14',
        selectedTime: '7:30 PM',
        tickets: { adult: 2, child: 0 },
        seats: [],
        paymentMethod: 'card',
        toggleSeat(seat) {
            if (this.seats.includes(seat)) {
                this.seats = this.seats.filter(s => s !== seat);
            } else {
                this.seats.push(seat);
            }
        },
        canContinue() {
            if (this.step === 1) return this.selectedDate && this.selectedTime;
            if (this.step === 2) return (this.tickets.adult + this.tickets.child) > 0;
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
                                @foreach (['Thu, Feb 13', 'Fri, Feb 14', 'Sat, Feb 15', 'Sun, Feb 16', 'Mon, Feb 17', 'Tue, Feb 18'] as $date)
                                    <button type="button"
                                        class="rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-slate-200 transition hover:border-accent/60"
                                        :class="selectedDate === '{{ $date }}' ? 'border-accent/60 bg-card' : ''"
                                        @click="selectedDate = '{{ $date }}'">
                                        {{ $date }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-white">Select Time</h4>
                            <div class="mt-4 flex flex-wrap gap-3">
                                @foreach (['4:30 PM', '6:00 PM', '7:30 PM', '9:05 PM'] as $time)
                                    <button type="button"
                                        class="rounded-full border border-white/10 bg-canvas-muted px-4 py-2 text-sm text-slate-200 transition hover:border-accent/60"
                                        :class="selectedTime === '{{ $time }}' ? 'border-accent/60 bg-card' : ''"
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
                        @foreach ([['label' => 'Adult', 'key' => 'adult', 'price' => 'LKR 1,200'], ['label' => 'Child', 'key' => 'child', 'price' => 'LKR 800']] as $ticket)
                            <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-canvas-muted px-5 py-4">
                                <div>
                                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">{{ $ticket['label'] }}</p>
                                    <p class="text-lg font-semibold text-white">{{ $ticket['price'] }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="tickets.{{ $ticket['key'] }} = Math.max(0, tickets.{{ $ticket['key'] }} - 1)">-</button>
                                    <span class="min-w-[2ch] text-center text-white" x-text="tickets.{{ $ticket['key'] }}"></span>
                                    <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="tickets.{{ $ticket['key'] }}++">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Step 3 -->
                <div x-show="step === 3" x-transition.opacity.duration.300>
                    <h4 class="text-lg font-semibold text-white">Select Seats</h4>
                    <p class="mt-2 text-sm text-slate-400">Pick your preferred seats. Selected seats: <span class="text-white" x-text="seats.join(', ') || 'None'"></span></p>
                    <div class="mt-6 grid grid-cols-6 gap-3 sm:grid-cols-8">
                        @foreach (['A','B','C','D','E'] as $row)
                            @for ($i = 1; $i <= 6; $i++)
                                @php($seat = $row.$i)
                                <button type="button"
                                    class="rounded-lg border border-white/10 bg-canvas-muted px-3 py-2 text-xs text-slate-200 transition hover:border-accent/60"
                                    :class="seats.includes('{{ $seat }}') ? 'border-accent/60 bg-card text-white' : ''"
                                    @click="toggleSeat('{{ $seat }}')">
                                    {{ $seat }}
                                </button>
                            @endfor
                        @endforeach
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
                                <p><span class="text-white">Tickets:</span> <span x-text="tickets.adult + tickets.child"></span></p>
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
