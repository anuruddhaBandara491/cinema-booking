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
        showErrors: false,
        errors: {
            name: '',
            phoneNumber: ''
        },
        validateStep1() {
            this.errors.name = this.userDetails.name.trim() === '' ? 'Full name is required.' : '';
            this.errors.phoneNumber = this.userDetails.phoneNumber.trim() === '' ? 'Phone number is required.' : '';
        },
        maxStep: 5,
        userDetails: {
            name: '',
            phoneNumber: '',
            email: '',
            nic: ''
        },
        selectedDate: @js($defaultDate),
        selectedTime: @js($defaultTime),
        tickets: @js($ticketCounts),
        boxTypeIds: @js($boxTypeIds),
        seats: [],
        paymentMethod: 'card',
        seatType(seat) {
            const row = seat?.toString().charAt(0).toUpperCase();
            return row === 'G' || row === 'H' ? 'box' : 'odc';
        },
        toggleSeat(seat) {
            if (this.seats.includes(seat)) {
                this.seats = this.seats.filter(s => s !== seat);
            } else {
                const type = this.seatType(seat);
                const ticketTotals = this.ticketTotalsByType();
                const seatTotals = this.seatTotalsByType();
                const boxCapacity = Math.floor(ticketTotals.box / 2);

                if (!ticketTotals[type]) {
                    return;
                }

                if (type === 'box' && seatTotals.box >= boxCapacity) {
                    return;
                }

                if (type === 'odc' && seatTotals.odc >= ticketTotals.odc) {
                    return;
                }

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
        ticketTotalsByType() {
            return Object.entries(this.tickets).reduce((totals, [id, counts]) => {
                const total = (counts.adult || 0) + (counts.child || 0);
                if (this.boxTypeIds.includes(Number(id))) {
                    totals.box += total;
                } else {
                    totals.odc += total;
                }
                return totals;
            }, { box: 0, odc: 0 });
        },
        seatTotalsByType() {
            return this.seats.reduce((totals, seat) => {
                const type = this.seatType(seat);
                totals[type] += 1;
                return totals;
            }, { box: 0, odc: 0 });
        },
        calculateTotal() {
            // This would need to calculate based on ticket types and prices
            // For now, returning a placeholder that will be calculated server-side
            return 0;
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
            if (this.step === 1) {
                this.validateStep1();
                return this.errors.name === '' && this.errors.phoneNumber === '';
            }
            if (this.step === 2) return this.selectedDate && this.selectedTime;
            if (this.step === 3) {
                const hasTickets = this.totalTickets() > 0;
                const boxValid = this.boxTypeIds.every((id) => this.isBoxValid(id));
                return hasTickets && boxValid;
            }
            if (this.step === 4) {
                const ticketTotals = this.ticketTotalsByType();
                const seatTotals = this.seatTotalsByType();
                const totalTickets = ticketTotals.box + ticketTotals.odc;
                const totalSeats = seatTotals.odc + (seatTotals.box * 2);
                const boxCapacity = Math.floor(ticketTotals.box / 2);

                if (totalTickets === 0) {
                    return false;
                }

                if (seatTotals.box > boxCapacity || seatTotals.odc > ticketTotals.odc) {
                    return false;
                }

                return totalSeats === totalTickets;
            }
            return true;
        }
    }">
        <form action="{{ route('bookings.store') }}" method="POST" @submit.prevent="step < maxStep ? step = Math.min(maxStep, step + 1) : $el.submit()" x-ref="bookingForm">
            @csrf

            <!-- Hidden fields for form submission -->
            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
            <input type="hidden" name="customer_name" x-model="userDetails.name">
            <input type="hidden" name="customer_phone" x-model="userDetails.phoneNumber">
            <input type="hidden" name="customer_email" x-model="userDetails.email">
            <input type="hidden" name="customer_nic" x-model="userDetails.nic">
            <input type="hidden" name="booking_date" x-model="selectedDate">
            <input type="hidden" name="booking_time" x-model="selectedTime">
            <input type="hidden" name="selected_seats" :value="JSON.stringify(seats)">
            <input type="hidden" name="tickets" :value="JSON.stringify(tickets)">
            <input type="hidden" name="payment_method" x-model="paymentMethod">
            <input type="hidden" name="total_amount" :value="calculateTotal()">

            <div class="card-surface p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Step <span x-text="step"></span> of <span x-text="maxStep"></span></p>
                    <h3 class="text-xl font-semibold text-white">Book your seats</h3>
                </div>
                <div class="w-full max-w-md">
                    <div class="flex items-center justify-between text-xs uppercase tracking-[0.2em] text-slate-400">
                        <span>Your Details</span>
                        <span>Payment</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-canvas-muted">
                        <div class="h-2 rounded-full bg-gradient-to-r from-primary-600 via-accent to-amber-400 transition-all" :style="`width: ${((step - 1) / (maxStep - 1)) * 100}%`"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-5 gap-2 text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">
                        <div :class="step >= 1 ? 'text-white' : ''">Details</div>
                        <div :class="step >= 2 ? 'text-white' : ''">Date</div>
                        <div :class="step >= 3 ? 'text-white' : ''">Tickets</div>
                        <div :class="step >= 4 ? 'text-white' : ''">Seats</div>
                        <div :class="step >= 5 ? 'text-white' : ''">Payment</div>
                    </div>
                </div>
            </div>

            <div class="mt-10 space-y-8">
                @include('bookings.steps.step-user-details')
                @include('bookings.steps.step-1')
                @include('bookings.steps.step-2')
                @include('bookings.steps.step-3')
                @include('bookings.steps.step-4')
            </div>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" class="btn-ghost" @click="step = Math.max(1, step - 1)" x-show="step > 1">Back</button>
                <div class="flex items-center gap-3 sm:ml-auto">
                    <button type="button" class="btn-ghost" @click="showErrors = true; canContinue() ? step = Math.min(maxStep, step + 1) : null" x-show="step < maxStep">Next</button>
                    <button type="submit" class="btn-primary" :disabled="!canContinue() || step !== maxStep" x-show="step === maxStep">Confirm Booking</button>
                    <a href="{{ url('/movies') }}" class="text-xs uppercase tracking-[0.2em] text-slate-400 hover:text-white">Cancel</a>
                </div>
            </div>
            </div>
        </form>
</x-app-layout>
