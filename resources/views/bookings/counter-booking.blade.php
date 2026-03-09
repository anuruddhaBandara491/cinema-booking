@php
    $odcRows = ['A', 'B', 'C', 'D', 'E', 'F'];
    $odcColsMap = collect($odcRows)
        ->mapWithKeys(fn ($row) => [$row => range(1, $row === 'F' ? 20 : 16)])
        ->all();
    $boxRows = ['G', 'H'];
    $boxLeft = range(1, 4);
    $boxRight = range(5, 8);
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
                        this.bookedSeats = data.booked;
                        this.lockedSeats = data.locked || [];
                        // Remove any selected seats that are now booked
                        this.selectedSeats = this.selectedSeats.filter(seat => !this.bookedSeats.includes(seat));
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
        calculateTotal() {
            // Base price per seat - you may need to adjust this based on your pricing logic
            const pricePerSeat = 500;
            return this.selectedSeats.length * pricePerSeat;
        },
        canBook() {
            return this.movieId && this.bookingDate && this.bookingTime && this.selectedSeats.length > 0;
        },
        confirmBooking() {
            if (!this.canBook()) {
                this.showMessage('Please select at least one seat', 'error');
                return;
            }

            if (!confirm(`Confirm booking for ${this.selectedSeats.length} seat(s)?`)) {
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
                    customer_name: this.customerName || null,
                    customer_phone: this.customerPhone || null,
                    total_amount: this.calculateTotal()
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isProcessing = false;
                if (data.success) {
                    this.showMessage(`Booking successful! Reference: ${data.booking.reference}`, 'success');
                    // Reset form
                    setTimeout(() => {
                        this.resetForm();
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
        resetForm() {
            this.selectedSeats = [];
            this.customerName = '';
            this.customerPhone = '';
            this.movieId = null;
            this.bookingDate = new Date().toISOString().split('T')[0];
            this.bookingTime = '';
            this.message = '';
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
                            <div class="h-4 w-4 rounded border border-red-400 bg-red-400"></div>
                            <span class="text-slate-400">Booked</span>
                        </div>
                    </div>

                    <!-- Theater Directions -->
                    <div class="mb-6 rounded-3xl border border-white/10 bg-canvas-muted p-6">
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

                        <!-- ODC Seats -->
                        <div class="mt-6 grid gap-2">
                            @foreach ($odcRows as $row)
                                @php($cols = $odcColsMap[$row] ?? [])
                                <div class="flex justify-center">
                                    <div class="inline-grid gap-2" style="grid-template-columns: repeat({{ count($cols) }}, minmax(0, 1fr));">
                                        @foreach ($cols as $col)
                                            @php($seat = $row.$col)
                                            <button type="button"
                                                class="h-6 w-6 rounded-md border border-white/40 bg-white/90 text-[10px] font-semibold text-slate-900 transition hover:border-emerald-400/70"
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

                        <!-- Screen Divider -->
                        <div class="mt-6 flex items-center justify-center">
                            <span class="h-1 w-24 rounded-full bg-slate-500/40"></span>
                        </div>

                        <!-- Box Seats -->
                        <div class="mt-5">
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Box</p>
                            <div class="mt-3 grid gap-3">
                                @foreach ($boxRows as $row)
                                    <div class="flex items-center justify-center gap-6">
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach ($boxLeft as $col)
                                                @php($seat = $row.$col)
                                                <button type="button"
                                                    class="rounded-md border border-white/40 bg-white/90 px-2 py-1.5 text-[10px] font-semibold text-slate-900 transition hover:border-emerald-400/70"
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

                                        <div class="h-10 w-2 rounded-full bg-slate-500/40"></div>

                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach ($boxRight as $col)
                                                @php($seat = $row.$col)
                                                <button type="button"
                                                    class="rounded-md border border-white/40 bg-white/90 px-2 py-1.5 text-[8px] font-semibold text-slate-900 transition hover:border-emerald-400/70"
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
                        <p class="text-lg font-semibold text-emerald-400" x-text="selectedSeats.length > 0 ? selectedSeats.join(', ') : 'None'"></p>
                    </div>

                    <!-- Total Amount -->
                    <div class="rounded-lg bg-slate-900/40 border border-slate-700/40 p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400 mb-2">Total Amount</p>
                        <p class="text-2xl font-bold text-white" x-text="`LKR ${calculateTotal().toLocaleString()}`"></p>
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
