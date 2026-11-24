@php
    $odcRows = ['A', 'B', 'C', 'D', 'E', 'F'];
    $odcColsMap = collect($odcRows)
        ->mapWithKeys(fn ($row) => [$row => range(1, $row === 'F' ? 20 : 16)])
        ->all();
    $boxRows = ['G', 'H'];
    $boxLeft = range(1, 4);
    $boxRight = range(5, 8);
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
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Counter Booking</p>
            <h2 class="text-2xl font-semibold text-white">Quick Booking System</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{
        movieId: null,
        bookingDate: '',
        bookingTime: '',
        selectedSeats: [],
        customerName: '',
        customerPhone: '',
        movies: @js($movies),
        tickets: @js($ticketCounts),
        emptyTickets: @js($ticketCounts),
        boxTypeIds: @js($boxTypeIds),
        ticketTypePrices: @js($ticketTypes->mapWithKeys(fn ($type) => [strtolower($type->name) => ['id' => $type->id, 'adult_price' => (float) $type->adult_price, 'child_price' => $type->has_child ? (float) $type->child_price : 0]])->all()),
        bookedSeats: [],
        lockedSeats: [],
        isProcessing: false,
        message: '',
        messageType: '',
        init() {
            // Set today's date as default

            const today = new Date();
            this.bookingDate = today.toISOString().split('T')[0];
            this.updateSeatStatus();
        },
        updateSeatStatus() {
            if (!this.movieId || !this.bookingDate || !this.bookingTime) {
                this.bookedSeats = [];
                this.lockedSeats = [];
                return;
            }

            const convertedTime = this.convertTimeTo24Hour(this.bookingTime);

            fetch(`/api/seats?movie_id=${this.movieId}&show_date=${this.bookingDate}&show_time=${convertedTime}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const lockedList = data.locked || [];
                        this.bookedSeats = data.booked || [];
                        this.lockedSeats = lockedList.map(lock => lock.seat_number || lock);
                        this.selectedSeats = this.selectedSeats.filter(seat => !this.bookedSeats.includes(seat) && !this.lockedSeats.includes(seat));
                    }
                })
                .catch(err => console.error('Failed to update seat status:', err));
        },
        convertTimeTo24Hour(time12Hour) {
            if (!time12Hour) return '';

            const match = time12Hour.match(/(\d{2}):(\d{2})(AM|PM)/i);
            if (!match) return time12Hour; // Return as-is if doesn't match format

            let hours = parseInt(match[1]);
            const minutes = match[2];
            const period = match[3].toUpperCase();

            if (period === 'PM' && hours !== 12) {
                hours += 12;
            } else if (period === 'AM' && hours === 12) {
                hours = 0;
            }

            return `${String(hours).padStart(2, '0')}:${minutes}`;
        },
        toggleSeat(seat) {
            if (this.bookedSeats.includes(seat)) {
                alert('This seat is already booked.');
                return;
            }

            if (this.lockedSeats.includes(seat) && !this.selectedSeats.includes(seat)) {
                alert('This seat is currently locked.');
                return;
            }

            if (this.selectedSeats.includes(seat)) {
                this.selectedSeats = this.selectedSeats.filter(s => s !== seat);
            } else {
                this.selectedSeats.push(seat);
            }
        },
        totalTickets() {
            return Object.values(this.tickets).reduce((sum, item) => {
                return sum + (item.adult || 0) + (item.child || 0);
            }, 0);
        },
        adultCount() {
            return Object.values(this.tickets).reduce((sum, item) => sum + (item.adult || 0), 0);
        },
        childCount() {
            return Object.values(this.tickets).reduce((sum, item) => sum + (item.child || 0), 0);
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
        seatType(seat) {
            const row = String(seat).charAt(0).toUpperCase();
            return ['G', 'H'].includes(row) ? 'box' : 'odc';
        },
        seatTotalsByType() {
            return this.selectedSeats.reduce((totals, seat) => {
                totals[this.seatType(seat)] += 1;
                return totals;
            }, { box: 0, odc: 0 });
        },
        priceForTypeId(id) {
            return Object.values(this.ticketTypePrices).find((prices) => Number(prices.id) === Number(id)) || null;
        },
        calculateSeatPrices() {
            let seatPrice = 0;

            for (const [id, counts] of Object.entries(this.tickets)) {
                const prices = this.priceForTypeId(id);
                if (!prices) {
                    continue;
                }

                seatPrice += (counts.adult || 0) * prices.adult_price;
                seatPrice += (counts.child || 0) * (prices.child_price || 0);
            }

            return seatPrice;
        },
        calculateTotal() {
            if (this.totalTickets() === 0) {
                return 0;
            }

            return this.calculateSeatPrices();
        },
        formatMoney(amount) {
            return `LKR ${Number(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
        seatMatchMessage() {
            if (this.selectedSeats.length === 0 && this.totalTickets() === 0) {
                return '';
            }

            if (!this.boxTypeIds.every((id) => this.isBoxValid(id))) {
                return 'Box tickets must be selected in pairs.';
            }

            const ticketTotals = this.ticketTotalsByType();
            const seatTotals = this.seatTotalsByType();

            if (seatTotals.odc !== ticketTotals.odc || seatTotals.box !== ticketTotals.box / 2) {
                return 'Adult and child counts must match the selected seats. One ODC seat is one person. One box seat covers two people.';
            }

            return '';
        },
        canBook() {
            return this.movieId && this.bookingDate && this.bookingTime && this.selectedSeats.length > 0 && this.totalTickets() > 0 && this.seatMatchMessage() === '';
        },
        confirmBooking() {
            if (!this.canBook()) {
                this.showMessage(this.seatMatchMessage() || 'Select seats and matching adult and child counts', 'error');
                return;
            }

            if (!confirm(`Confirm booking for ${this.adultCount()} adult(s) and ${this.childCount()} child(ren)? Total ${this.formatMoney(this.calculateTotal())}`)) {
                return;
            }

            this.isProcessing = true;
            this.message = '';

            const convertedTime = this.convertTimeTo24Hour(this.bookingTime);

            fetch('{{ route("bookings.counter.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    movie_id: this.movieId,
                    booking_date: this.bookingDate,
                    booking_time: convertedTime,
                    selected_seats: this.selectedSeats,
                    tickets: this.tickets,
                    customer_name: this.customerName || null,
                    customer_phone: this.customerPhone || null
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isProcessing = false;
                if (data.success) {
                    const guestSummary = data.booking.tickets ? ` ${data.booking.tickets}.` : '';
                    this.showMessage(`Booking successful! Reference: ${data.booking.reference}.${guestSummary} Total ${this.formatMoney(data.booking.total_amount)}.`, 'success');
                    setTimeout(() => {
                        this.resetForm(true);
                    }, 1500);
                } else {
                    this.showMessage(data.message, 'error');
                }
            })
            .catch(err => {
                this.isProcessing = false;
                console.error('Booking error:', err);
                this.showMessage('An error occurred while processing the booking', 'error');
            });
        },
        resetForm(keepMessage = false) {
            this.selectedSeats = [];
            this.customerName = '';
            this.customerPhone = '';
            this.movieId = null;
            this.bookingDate = new Date().toISOString().split('T')[0];
            this.bookingTime = '';
            this.tickets = JSON.parse(JSON.stringify(this.emptyTickets));
            if (!keepMessage) {
                this.message = '';
            }
            this.updateSeatStatus();
        },
        showMessage(msg, type) {
            this.message = msg;
            this.messageType = type;
            if (type === 'success') {
                setTimeout(() => {
                    this.message = '';
                }, 3000);
            }
        },
        getMovieTitle(movieId) {
            const movie = this.movies.find(m => m.id == movieId);
            return movie ? movie.title : 'Select a movie';
        },
        getMovieTimes(movieId) {
            const movie = this.movies.find(m => m.id == movieId);
            return movie ? (movie.show_times || []) : [];
        }
    }" @init="init()">
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Side: Seat Map -->
            <div class="lg:col-span-2">
                <!-- Seat Map Card -->
                <div class="card-surface p-6 sm:p-8">
                    <h3 class="text-lg font-semibold text-white mb-6">Theater Layout</h3>

                    <!-- Seat Legend -->
                    <div class="mb-6 flex flex-wrap gap-4 text-xs">
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

                    <!-- Theater Directions -->
                    <div class="mb-6 rounded-3xl border border-white/10 bg-canvas-muted p-3 sm:p-6">
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
                                                            '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': selectedSeats.includes('{{ $seat }}'),
                                                            '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                                            '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}')
                                                        }"
                                                        @click="toggleSeat('{{ $seat }}')"
                                                        :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}'))">
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
                                                        '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': selectedSeats.includes('{{ $seat }}'),
                                                        '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                                        '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}')
                                                    }"
                                                    @click="toggleSeat('{{ $seat }}')"
                                                    :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}'))">
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
                                                        '!border-emerald-400 !bg-emerald-400 !text-emerald-950 shadow-[0_0_12px_rgba(52,211,153,0.45)]': selectedSeats.includes('{{ $seat }}'),
                                                        '!border-red-400 !bg-red-400 !text-red-950 cursor-not-allowed': bookedSeats.includes('{{ $seat }}'),
                                                        '!border-yellow-400 !bg-yellow-400 !text-yellow-950 cursor-not-allowed': lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}')
                                                    }"
                                                    @click="toggleSeat('{{ $seat }}')"
                                                    :disabled="bookedSeats.includes('{{ $seat }}') || (lockedSeats.includes('{{ $seat }}') && !selectedSeats.includes('{{ $seat }}'))">
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
            </div>

            <!-- Right Side: Booking Form -->
            <div class="lg:col-span-1">
                <!-- Message Alert -->
                <div x-show="message" :class="messageType === 'success' ? 'bg-emerald-500/20 border-emerald-500/50' : 'bg-red-500/20 border-red-500/50'" class="mb-4 rounded-lg border p-4 text-sm">
                    <p :class="messageType === 'success' ? 'text-emerald-100' : 'text-red-100'" x-text="message"></p>
                </div>

                <!-- Booking Form Card -->
                <div class="card-surface sticky top-6 space-y-4 p-6">
                    <h3 class="text-lg font-semibold text-white">Booking Details</h3>

                    <!-- Movie Selection -->
                    <div>
                        <label class="block text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Movie</label>
                        <select x-model="movieId" @change="updateSeatStatus()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder-slate-500">
                            <option value="">Select a movie</option>
                            @foreach ($movies as $movie)
                                <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Booking Date -->
                    <div>
                        <label class="block text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Date</label>
                        <input type="date" x-model="bookingDate" @change="updateSeatStatus()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white">
                    </div>

                    <!-- Booking Time -->
                    <div>
                        <label class="block text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Time</label>
                        <select x-model="bookingTime" @change="updateSeatStatus()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder-slate-500" :disabled="!movieId">
                            <option value="">Select a time</option>
                            <template x-for="time in getMovieTimes(movieId)" :key="time">
                                <option :value="time" x-text="time"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Selected Seats Display -->
                    <div class="rounded-lg bg-slate-900/40 border border-slate-700/40 p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Selected Seats</p>
                        <p id="counterBooking__selected_seats" class="text-lg font-semibold text-emerald-400" x-text="selectedSeats.length > 0 ? selectedSeats.join(', ') : 'None'"></p>
                    </div>

                    <!-- Adult and child counts -->
                    <div class="space-y-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Adult and child counts</p>
                        @forelse ($ticketTypes as $ticketType)
                            @php($ticketKey = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $ticketType->name)))
                            <div class="rounded-lg border border-slate-700/40 bg-slate-900/40 p-3">
                                <p class="text-sm uppercase tracking-[0.2em] text-slate-300">{{ $ticketType->name }}</p>
                                @if (strtolower($ticketType->name) === 'box')
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-amber-300">Pairs only. One box seat covers two people.</p>
                                @endif
                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Adult</p>
                                        <p class="text-sm font-semibold text-white">LKR {{ number_format($ticketType->adult_price, 2) }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" id="counterBooking__{{ $ticketKey }}_adult_decrement" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="decrementTicket({{ $ticketType->id }}, 'adult')">-</button>
                                        <span id="counterBooking__{{ $ticketKey }}_adult_count" class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].adult"></span>
                                        <button type="button" id="counterBooking__{{ $ticketKey }}_adult_increment" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="incrementTicket({{ $ticketType->id }}, 'adult')">+</button>
                                    </div>
                                </div>
                                @if ($ticketType->has_child)
                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Child</p>
                                            <p class="text-sm font-semibold text-white">LKR {{ number_format($ticketType->child_price, 2) }}</p>
                                            @if (strtolower($ticketType->name) === 'odc')
                                                <p class="mt-1 text-[11px] uppercase tracking-[0.16em] text-slate-500">Ages 2-13 only</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" id="counterBooking__{{ $ticketKey }}_child_decrement" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="decrementTicket({{ $ticketType->id }}, 'child')">-</button>
                                            <span id="counterBooking__{{ $ticketKey }}_child_count" class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].child"></span>
                                            <button type="button" id="counterBooking__{{ $ticketKey }}_child_increment" class="h-8 w-8 rounded-full border border-white/10 text-white" @click="incrementTicket({{ $ticketType->id }}, 'child')">+</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-lg border border-white/10 bg-slate-900/40 p-3 text-sm text-slate-400">
                                No ticket prices are set up yet.
                            </div>
                        @endforelse
                        <p id="counterBooking__seat_match" x-show="seatMatchMessage()" x-cloak class="text-xs text-amber-300" x-text="seatMatchMessage()"></p>
                    </div>

                    <!-- Guest counts and total -->
                    <div class="rounded-lg bg-slate-900/40 border border-slate-700/40 p-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Adults</span>
                            <span id="counterBooking__adult_count" class="font-semibold text-white" x-text="adultCount()"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400">Children</span>
                            <span id="counterBooking__child_count" class="font-semibold text-white" x-text="childCount()"></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-700/60 pt-2">
                            <span class="text-xs uppercase tracking-[0.2em] text-slate-400">Total</span>
                            <span id="counterBooking__total_amount" class="text-2xl font-bold text-white" x-text="formatMoney(calculateTotal())"></span>
                        </div>
                    </div>

                    <!-- Customer Name (Optional) -->
                    <div>
                        <label class="block text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Customer Name (Optional)</label>
                        <input type="text" x-model="customerName" placeholder="e.g., John Doe" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder-slate-500">
                    </div>

                    <!-- Customer Phone (Optional) -->
                    <div>
                        <label class="block text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Phone (Optional)</label>
                        <input type="tel" x-model="customerPhone" placeholder="e.g., 0712345678" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder-slate-500">
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-4">
                        <button @click="confirmBooking()" :disabled="!canBook() || isProcessing" class="w-full rounded-lg bg-gradient-to-r from-primary-600 via-accent to-amber-400 px-4 py-3 font-semibold text-white transition hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isProcessing">Confirm Booking</span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                        <button @click="resetForm()" class="w-full rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Reset Form
                        </button>
                    </div>

                    <!-- Payment Info -->
                    <div class="rounded-lg bg-emerald-900/20 border border-emerald-700/30 p-3">
                        <p class="text-xs font-semibold text-emerald-300">✓ Cash Payment</p>
                        <p class="text-xs text-emerald-200 mt-1">Payment status will be marked as "Paid"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
