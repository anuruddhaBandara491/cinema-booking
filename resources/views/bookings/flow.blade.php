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
        movieId: {{ $movie->id }},
        sessionId: '',
        lockedSeats: [],
        bookedSeats: [],
        globalLockTimer: 0, // Single global timer for all selected seats
        timerInterval: null,
        pollingInterval: null,
        init() {
            this.initSessionId();
            this.startPolling();
            this.startTimerUpdates();
        },
        destroy() {
            this.stopPolling();
            this.stopTimerUpdates();
        },
        initSessionId() {
            // Generate unique session ID for this booking session
            this.sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
            console.log('Session ID initialized:', this.sessionId);
        },
        startTimerUpdates() {
            // Update global timer every second
            this.timerInterval = setInterval(() => {
                if (this.globalLockTimer > 0) {
                    this.globalLockTimer--;
                    if (this.globalLockTimer <= 0) {
                        // Timer expired, clear all selected seats
                        this.seats = [];
                        console.log('Global lock timer expired. All seats deselected.');
                    }
                }
            }, 1000);
        },
        stopTimerUpdates() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },
        startPolling() {
            // Poll for seat status every 3 seconds
            this.pollingInterval = setInterval(() => {
                this.updateSeatStatus();
            }, 3000);
        },
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },
        updateSeatStatus() {
            const params = new URLSearchParams({
                movie_id: this.movieId,
                show_date: this.parseDate(this.selectedDate),
                show_time: this.parseTime(this.selectedTime)
            });

            fetch(`/api/seats?${params}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update locked seats list
                        const newLockedSeats = data.locked.map(l => l.seat_number);
                        this.lockedSeats = newLockedSeats;
                        
                        // Update global timer if user has selected seats
                        if (this.seats.length > 0 && data.locked.length > 0) {
                            // Get the remaining time from any of the locked seats
                            const firstLockedSeat = data.locked[0];
                            if (firstLockedSeat && firstLockedSeat.remaining_seconds) {
                                this.globalLockTimer = firstLockedSeat.remaining_seconds;
                            }
                        }
                        
                        this.bookedSeats = data.booked;
                    }
                })
                .catch(err => console.error('Polling error:', err));
        },
        parseDate(dateStr) {
            const today = new Date();
            const year = today.getFullYear();

            const parts = dateStr.split(', ');
            if (parts.length === 2) {
                const monthDay = parts[1];
                const monthDayParts = monthDay.split(' ');
                const monthStr = monthDayParts[0];
                const day = monthDayParts[1];

                const months = { 'Jan': 1, 'Feb': 2, 'Mar': 3, 'Apr': 4, 'May': 5, 'Jun': 6,
                                 'Jul': 7, 'Aug': 8, 'Sep': 9, 'Oct': 10, 'Nov': 11, 'Dec': 12 };
                const month = months[monthStr] || today.getMonth() + 1;

                return `${year}-${String(month).padStart(2, '0')}-${day}`;
            }

            // Fallback: use today's date
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const dayNum = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${dayNum}`;
        },
        parseTime(timeStr) {
            // Extract HH:MM format from timeStr
            if (!timeStr) return '14:30';

            // Remove spaces
            timeStr = timeStr.trim();

            
            if (/^\d{1,2}:\d{2}(?:AM|PM|am|pm)$/.test(timeStr)) {
                const isPM = /PM|pm/.test(timeStr);
                const isAM = /AM|am/.test(timeStr);

                // Remove AM/PM
                const timeOnly = timeStr.replace(/AM|PM|am|pm/g, '');
                const parts = timeOnly.split(':');
                let hours = parseInt(parts[0], 10);
                const minutes = parts[1];

                // Convert to 24-hour format
                if (isPM && hours !== 12) {
                    hours += 12;
                } else if (isAM && hours === 12) {
                    hours = 0;
                }

                return `${String(hours).padStart(2, '0')}:${minutes}`;
            }

            // If it's already in HH:MM format, return as is
            if (/^\d{1,2}:\d{2}$/.test(timeStr)) {
                const parts = timeStr.split(':');
                const hours = String(parts[0]).padStart(2, '0');
                const minutes = String(parts[1]).padStart(2, '0');
                return `${hours}:${minutes}`;
            }

            return timeStr;
        },
        seatType(seat) {
            const row = seat?.toString().charAt(0).toUpperCase();
            return row === 'G' || row === 'H' ? 'box' : 'odc';
        },
        toggleSeat(seat) {
            // Check if seat is already booked or locked by another user
            if (this.bookedSeats.includes(seat)) {
                alert('This seat is already booked.');
                return;
            }

            const lockedByOther = this.lockedSeats.includes(seat) && !this.seats.includes(seat);
            if (lockedByOther) {
                alert('This seat is locked by another user.');
                return;
            }

            if (this.seats.includes(seat)) {
                // Release the seat lock
                this.releaseSeat(seat);
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

                // Lock the seat
                this.lockSeat(seat);
                this.seats.push(seat);
            }
        },
        lockSeat(seat) {
            const isFirstSeat = this.seats.length === 0;
            
            fetch('/api/lock-seat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    movie_id: this.movieId,
                    show_date: this.parseDate(this.selectedDate),
                    show_time: this.parseTime(this.selectedTime),
                    seat_number: seat,
                    session_id: this.sessionId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to lock seat:', data.message);
                    // Remove from seats array if lock failed
                    this.seats = this.seats.filter(s => s !== seat);
                    alert(data.message);
                } else {
                    // Initialize global timer only on first seat selection
                    if (isFirstSeat) {
                        this.globalLockTimer = data.remaining_seconds || 300;
                        console.log('First seat locked. Timer started:', this.globalLockTimer);
                    }
                }
            })
            .catch(err => console.error('Lock error:', err));
        },
        releaseSeat(seat) {
            fetch('/api/release-seat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    movie_id: this.movieId,
                    show_date: this.parseDate(this.selectedDate),
                    show_time: this.parseTime(this.selectedTime),
                    seat_number: seat,
                    session_id: this.sessionId
                })
            })
            .catch(err => console.error('Release error:', err));
        },
        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${String(secs).padStart(2, '0')}`;
        },
        getMaxLockTime() {
            // Return the global lock timer
            return this.globalLockTimer;
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
    }" @init="init()" @destroy="destroy()">
        <form action="{{ route('bookings.store') }}" method="POST" @submit.prevent="step < maxStep ? step = Math.min(maxStep, step + 1) : $el.submit()" x-ref="bookingForm">
            @csrf

            <!-- Hidden fields for form submission -->
            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
            <input type="hidden" name="customer_name" x-model="userDetails.name">
            <input type="hidden" name="customer_phone" x-model="userDetails.phoneNumber">
            <input type="hidden" name="customer_email" x-model="userDetails.email">
            <input type="hidden" name="customer_nic" x-model="userDetails.nic">
            <input type="hidden" name="booking_date" :value="parseDate(selectedDate)">
            <input type="hidden" name="booking_time" :value="parseTime(selectedTime)">
            <input type="hidden" name="selected_seats" :value="JSON.stringify(seats)">
            <input type="hidden" name="tickets" :value="JSON.stringify(tickets)">
            <input type="hidden" name="session_id" x-model="sessionId">
            <input type="hidden" name="payment_method" x-model="paymentMethod">
            <input type="hidden" name="total_amount" :value="calculateTotal()">

            <div class="card-surface p-6 sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Step <span x-text="step"></span> of <span x-text="maxStep"></span></p>
                            <h3 class="text-xl font-semibold text-white">Book your seats</h3>
                        </div>
                        <!-- Timer Display -->
                        <div x-show="getMaxLockTime() > 0" class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-900/50 to-amber-800/50 px-3 py-2 sm:px-4 sm:py-3 border border-amber-700/50">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-amber-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-[10px] sm:text-xs uppercase tracking-[0.2em] text-amber-400">Seat Lock</p>
                                <p class="font-mono text-sm sm:text-base font-bold text-amber-100" x-text="formatTime(getMaxLockTime())"></p>
                            </div>
                        </div>
                    </div>
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
