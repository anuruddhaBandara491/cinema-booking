@php
    $today = now()->toDateString();
    $showStartDate = optional($movie->show_start_date)->toDateString();
    $showEndDate = optional($movie->show_end_date)->toDateString();

    // Determine if movie is expired (today is after show_end_date)
    $isExpired = $today > $showEndDate;
    $isShowActive = $today <= $showEndDate;

    // Determine the start date for the booking window
    // If show hasn't started, start from show_start_date. Otherwise, start from today.
    $bookingStartDate = $today < $showStartDate ? $showStartDate : $today;

    // Generate dates based on show period constraints
    $windowDays = max(1, (int) ($movie->booking_window_days ?? 3));
    $dates = collect(range(0, $windowDays - 1))
        ->map(function ($offset) use ($bookingStartDate, $showEndDate) {
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingStartDate)->addDays($offset);
            // Don't generate dates beyond show_end_date
            if ($date->toDateString() <= $showEndDate) {
                return $date->format('D, M d');
            }
            return null;
        })
        ->filter(fn ($date) => $date !== null)
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
        termsAccepted: false,
        verificationAccepted: false,
        isExpired: @js($isExpired),
        isShowActive: @js($isShowActive),
        errors: {
            name: '',
            phoneNumber: '',
            email: ''
        },
        validateStep3() {
            this.errors.name = this.userDetails.name.trim() === '' ? 'Full name is required.' : '';
            this.errors.phoneNumber = this.userDetails.phoneNumber.trim() === '' ? 'Phone number is required.' : '';

            // Validate email if provided
            if (this.userDetails.email.trim() !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                this.errors.email = !emailRegex.test(this.userDetails.email) ? 'Please enter a valid email address.' : '';
            } else {
                this.errors.email = '';
            }
        },
        maxStep: 3,
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
        movieName: @js($movie->title),
        movieBookingCharge: {{ $movie->booking_charge ?? 0 }},
        ticketTypePrices: @js($ticketTypes->mapWithKeys(fn ($type) => [strtolower($type->name) => ['id' => $type->id, 'adult_price' => (float)$type->adult_price, 'child_price' => $type->has_child ? (float)$type->child_price : 0]])->all()),
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
        async logBookingEvent(event, data = {}) {
            try {
                const response = await fetch(`/api/log/${event}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        session_id: this.sessionId,
                        ...data
                    })
                });

                if (!response.ok) {
                    console.warn(`Failed to log ${event}:`, response.statusText);
                }
            } catch (error) {
                console.error(`Error logging ${event}:`, error);
            }
        },
        startTimerUpdates() {
            // Update global timer every second
            this.timerInterval = setInterval(() => {
                if (this.globalLockTimer > 0) {
                    this.globalLockTimer--;
                    if (this.globalLockTimer <= 0) {
                        // Timer expired, release all seats at once
                        this.releaseAllLockedSeats();
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
        releaseAllLockedSeats() {
            // Release all seats at once when timer expires
            if (this.seats.length === 0) {
                return;
            }

            const seatsToRelease = [...this.seats];

            fetch('/api/release-all-seats', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: this.sessionId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Clear all seats from the UI
                    this.seats = [];
                    this.globalLockTimer = 0;

                    // Notify user
                    alert(`Your seat locks have expired!\n\nSeats released: ${seatsToRelease.join(', ')}\n\nPlease select seats again to continue booking.`);
                    console.log('All locked seats released due to timer expiry:', seatsToRelease);
                }
            })
            .catch(err => console.error('Release all error:', err));
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
            let total = 0;

            // Calculate seat prices based on selected seats and ticket counts
            for (const seat of this.seats) {
                const seatType = this.seatType(seat);
                if (seatType === 'box') {
                    if (this.ticketTypePrices.box) {
                        total += this.ticketTypePrices.box.adult_price;
                    }
                } else if (seatType === 'odc') {
                    if (this.ticketTypePrices.odc) {
                        total += this.ticketTypePrices.odc.adult_price;
                    }
                }
            }

            // Add booking charge
            total += this.movieBookingCharge;

            return total.toFixed(2);
        },
        calculateSeatPrices() {
            let seatPrice = 0;
            for (const seat of this.seats) {
                const seatType = this.seatType(seat);
                if (seatType === 'box') {
                    if (this.ticketTypePrices.box) {
                        seatPrice += this.ticketTypePrices.box.adult_price;
                    }
                } else if (seatType === 'odc') {
                    if (this.ticketTypePrices.odc) {
                        seatPrice += this.ticketTypePrices.odc.adult_price;
                    }
                }
            }
            return seatPrice.toFixed(2);
        },
        calculateBookingCharge() {
            return this.movieBookingCharge.toFixed(2);
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
                // Step 1: Check if show is expired, then validate date/time + tickets
                if (this.isExpired) {
                    return false;
                }
                const hasDate = this.selectedDate && this.selectedTime;
                const hasTickets = this.totalTickets() > 0;
                const boxValid = this.boxTypeIds.every((id) => this.isBoxValid(id));
                return hasDate && hasTickets && boxValid;
            }
            if (this.step === 2) {
                // Step 2: Seats selection
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
            if (this.step === 3) {
                // Step 3: Confirm Booking - enable only when both checkboxes are checked
                return this.termsAccepted && this.verificationAccepted;
            }
            return true;
        }
    }" @init="init()" @destroy="destroy()">
        <form action="{{ route('bookings.store') }}" method="POST" @submit.prevent="step < maxStep ? step = Math.min(maxStep, step + 1) : $el.submit()" x-ref="bookingForm">
            @csrf

            <!-- Hidden fields for form submission -->
            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
            <input type="hidden" name="movie_name" x-model="movieName">
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
            <input type="hidden" name="total_amount" :value="Math.round(calculateTotal() * 100) / 100">

            <!-- Handle step transitions with logging -->
            <script>
                function handleStepTransition(currentStep, nextStep, data) {
                    const stepActions = {
                        1: () => Alpine.$data(document.querySelector('[x-data*="step"]')).logBookingEvent('user-details', data),
                        2: () => Alpine.$data(document.querySelector('[x-data*="step"]')).logBookingEvent('movie-selection', data),
                        3: () => Alpine.$data(document.querySelector('[x-data*="step"]')).logBookingEvent('ticket-count', data),
                        4: () => Alpine.$data(document.querySelector('[x-data*="step"]')).logBookingEvent('seat-selection', data),
                        5: () => Alpine.$data(document.querySelector('[x-data*="step"]')).logBookingEvent('payment-attempt', data)
                    };

                    if (stepActions[nextStep]) {
                        stepActions[nextStep]();
                    }
                }
            </script>

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
                        <span>Book Tickets</span>
                        <span>Your Details</span>
                    </div>
                    <div class="mt-2 h-2 w-full rounded-full bg-canvas-muted">
                        <div class="h-2 rounded-full bg-gradient-to-r from-primary-600 via-accent to-amber-400 transition-all" :style="`width: ${((step - 1) / (maxStep - 1)) * 100}%`"></div>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">
                        <div :class="step >= 1 ? 'text-white' : ''">Date & Tickets</div>
                        <div :class="step >= 2 ? 'text-white' : ''">Seats</div>
                        <div :class="step >= 3 ? 'text-white' : ''">Details & Confirm</div>
                    </div>
                </div>
            </div>

            <div class="mt-10 space-y-8">
                @include('bookings.steps.step-1')
                @include('bookings.steps.step-2')
                @include('bookings.steps.step-3')
                @include('bookings.steps.step-4')
            </div>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Mobile: Next button first (full width) -->
                <div class="order-first sm:order-none w-full sm:w-auto flex flex-col gap-2 sm:gap-0 sm:hidden">
                    <button type="button" class="btn-ghost w-full" @click="
                        showErrors = true;
                        if (canContinue()) {
                            const currentStep = step;
                            const nextStep = Math.min(maxStep, step + 1);

                            // Log based on current step
                            if (currentStep === 1) {
                                logBookingEvent('movie-selection', {
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                });
                                logBookingEvent('ticket-count', {
                                    ticket_count: totalTickets(),
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                });
                            } else if (currentStep === 2) {
                                logBookingEvent('seat-selection', {
                                    selected_seats: seats,
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                    ticket_count: totalTickets(),
                                });
                            }

                            step = nextStep;
                        }
                    " x-show="step < maxStep">Next</button>
                    <button type="submit" class="btn-primary w-full" :disabled="!canContinue() || step !== maxStep" @click="
                        logBookingEvent('user-details', {
                            user_name: userDetails.name,
                            phone_number: userDetails.phoneNumber,
                            email: userDetails.email
                        });
                    " x-show="step === maxStep">Confirm Booking</button>
                </div>

                <!-- Desktop: Back button on left -->
                <button type="button" class="btn-ghost hidden sm:inline-block" @click="step = Math.max(1, step - 1)" x-show="step > 1">Back</button>

                <!-- Desktop: Next and Cancel buttons on right -->
                <div class="hidden sm:flex items-center gap-3">
                    <button type="button" class="btn-ghost" @click="
                        showErrors = true;
                        if (canContinue()) {
                            const currentStep = step;
                            const nextStep = Math.min(maxStep, step + 1);

                            // Log based on current step
                            if (currentStep === 1) {
                                logBookingEvent('movie-selection', {
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                });
                                logBookingEvent('ticket-count', {
                                    ticket_count: totalTickets(),
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                });
                            } else if (currentStep === 2) {
                                logBookingEvent('seat-selection', {
                                    selected_seats: seats,
                                    movie_id: movieId,
                                    show_date: parseDate(selectedDate),
                                    show_time: parseTime(selectedTime),
                                    ticket_count: totalTickets(),
                                });
                            }

                            step = nextStep;
                        }
                    " x-show="step < maxStep">Next</button>
                    <button type="submit" class="btn-primary" :disabled="!canContinue() || step !== maxStep" @click="
                        logBookingEvent('user-details', {
                            user_name: userDetails.name,
                            phone_number: userDetails.phoneNumber,
                            email: userDetails.email
                        });
                    " x-show="step === maxStep">Confirm Booking</button>
                    <a href="{{ url('/movies') }}" class="text-xs uppercase tracking-[0.2em] text-slate-400 hover:text-white">Cancel</a>
                </div>

                <!-- Mobile: Back and Cancel buttons below -->
                <div class="flex items-center gap-3 w-full sm:hidden">
                    <button type="button" class="btn-ghost flex-1" @click="step = Math.max(1, step - 1)" x-show="step > 1">Back</button>
                    <a href="{{ url('/movies') }}" class="text-xs uppercase tracking-[0.2em] text-slate-400 hover:text-white flex-1 text-center">Cancel</a>
                </div>
            </div>
            </div>
        </form>
</x-app-layout>
