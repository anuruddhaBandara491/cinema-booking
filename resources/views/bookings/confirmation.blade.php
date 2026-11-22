<x-app-layout>
    <style>
        @media print {
            @page {
                margin: 14mm;
            }

            html,
            body,
            .min-h-screen,
            main,
            section {
                background: #fff !important;
                color: #111827 !important;
            }

            .no-print {
                display: none !important;
            }

            .ticket-print,
            .ticket-print * {
                color: #111827 !important;
                box-shadow: none !important;
            }

            .ticket-print .rounded-2xl {
                background: #fff !important;
                border-color: #d1d5db !important;
                break-inside: avoid;
            }
        }
    </style>

    @php
        $paymentStatus = $booking->payment_status;
        $isPaid = $paymentStatus === 'completed';
        $isPending = $paymentStatus === 'pending';
        $statusClass = match ($paymentStatus) {
            'completed' => 'bg-emerald-500/20 text-emerald-300',
            'pending' => 'bg-yellow-500/20 text-yellow-300',
            default => 'bg-rose-500/20 text-rose-200',
        };
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Booking</p>
            <h2 class="text-2xl font-semibold text-white">
                @if ($isPaid)
                    Booking Confirmed
                @elseif ($isPending)
                    Payment Processing
                @else
                    Payment Not Completed
                @endif
            </h2>
        </div>
    </x-slot>

    <section id="bookingConfirmation__ticket" class="ticket-print mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="no-print mb-6 rounded-2xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                {{ session('error') }}
            </div>
        @endif

        @if ($isPaid)
            <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-emerald-400">Thank You!</h3>
                <p class="mt-2 text-slate-300">Your payment was received and the seats are booked.</p>
                @if ($booking->customer_email)
                    <p class="mt-2 text-sm text-slate-300">A copy of this ticket was sent to {{ $booking->customer_email }}.</p>
                @endif
            </div>
        @elseif ($isPending)
            <div class="rounded-2xl border border-yellow-400/20 bg-yellow-500/10 p-6 text-center">
                <h3 class="text-xl font-semibold text-yellow-200">Waiting for PayHere</h3>
                <p class="mt-2 text-slate-300">If you completed the payment, refresh this page in a moment. The booking is confirmed only after PayHere notifies this site.</p>
            </div>
        @else
            <div class="rounded-2xl border border-rose-400/20 bg-rose-500/10 p-6 text-center">
                <h3 class="text-xl font-semibold text-rose-200">Payment was not completed</h3>
                <p class="mt-2 text-slate-300">These seats are not reserved. You can start the booking again.</p>
            </div>
        @endif

        <!-- Booking Reference -->
        <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Booking Reference</p>
                    <p class="mt-2 font-mono text-2xl font-bold text-white">{{ $booking->booking_reference }}</p>
                </div>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $booking->booking_reference }}')" class="no-print rounded-lg border border-white/10 bg-white/10 px-4 py-2 text-sm text-white transition hover:bg-white/20">
                    Copy Reference
                </button>
            </div>
        </div>

        <!-- Booking Details Grid -->
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <!-- Customer Details -->
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Customer Details</p>
                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <p class="text-slate-500">Full Name</p>
                        <p class="text-white">{{ $booking->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Phone Number</p>
                        <p class="text-white">{{ $booking->customer_phone }}</p>
                    </div>
                    @if ($booking->customer_email)
                        <div>
                            <p class="text-slate-500">Email</p>
                            <p class="text-white">{{ $booking->customer_email }}</p>
                        </div>
                    @endif
                    @if ($booking->customer_nic)
                        <div>
                            <p class="text-slate-500">NIC / ID Number</p>
                            <p class="text-white">{{ $booking->customer_nic }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Booking Information -->
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Booking Information</p>
                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <p class="text-slate-500">Movie</p>
                        <p class="text-white">{{ $booking->movie->title }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Date & Time</p>
                        <p class="text-white">{{ $booking->booking_date->format('D, M d, Y') }} at {{ $booking->booking_time }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Booked At</p>
                        <p class="text-white">{{ $booking->booked_at ? $booking->booked_at->format('M d, Y H:i A') : 'Not confirmed yet' }}</p>
                    </div>
                </div>
            </div>

            <!-- Seats & Tickets -->
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Selected Seats</p>
                <div class="mt-4">
                    <p class="text-white">
                        {{ implode(', ', $booking->selected_seats) }}
                    </p>
                    <p class="mt-2 text-xs text-slate-400">{{ count($booking->selected_seats) }} seat(s)</p>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Payment</p>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-slate-300">Amount</p>
                        <p class="font-semibold text-white">LKR {{ number_format($booking->total_amount, 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-slate-300">Method</p>
                        <p class="text-white capitalize">{{ $booking->payment_method }}</p>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-white/10">
                        <p class="text-slate-300">Status</p>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="no-print mt-8 flex flex-col gap-3 sm:flex-row">
            @if ($isPaid)
                <button type="button" id="bookingConfirmation__print_ticket" onclick="window.print()" class="btn-primary flex-1 text-center">
                    Print ticket
                </button>
            @endif
            <a href="{{ route('movies.index') }}" class="btn-ghost flex-1 text-center">
                Browse movies
            </a>
            <a href="{{ route('contact') }}" class="btn-ghost flex-1 text-center">
                Contact the cinema
            </a>
        </div>

        <!-- Important Notice -->
        <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-6">
            <p class="text-sm text-slate-300">
                <strong class="text-white">Important:</strong> Save booking reference <strong>{{ $booking->booking_reference }}</strong>.
                Bring it to the cinema and arrive at least 30 minutes before the show.
                The ticket amount is not refunded, and the seat number cannot be changed.
            </p>
        </div>
    </section>
</x-app-layout>
