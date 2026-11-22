<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Staff</p>
            <h2 class="text-2xl font-semibold text-white">Bookings</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">
                {{ session('error') }}
            </div>
        @endif

        <p class="mb-6 text-sm text-slate-400">
            Cancelling a booking frees the seats. Return cash at the counter, or refund a card payment from the PayHere dashboard. This page does not send the money back.
        </p>

        <form method="GET" action="{{ route('bookings.manage') }}" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="bookingManage__show_date" class="text-xs uppercase tracking-[0.2em] text-slate-400">Show date</label>
                <input id="bookingManage__show_date" name="show_date" type="date" value="{{ $showDate }}" class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">
            </div>
            <div class="flex-1">
                <label for="bookingManage__search" class="text-xs uppercase tracking-[0.2em] text-slate-400">Reference or phone</label>
                <input id="bookingManage__search" name="search" type="text" value="{{ $search }}" placeholder="BK2026 or 077" class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-white">
            </div>
            <button type="submit" class="btn-primary">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-slate-900/80 text-left text-xs uppercase tracking-[0.15em] text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Film</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Seats</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse ($bookings as $booking)
                        @php
                            $canCancel = in_array($booking->payment_status, ['pending', 'completed', 'paid'], true);
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-4 font-mono text-white">{{ $booking->booking_reference }}</td>
                            <td class="px-4 py-4 text-slate-200">
                                <p>{{ $booking->customer_name }}</p>
                                <p class="text-xs text-slate-400">{{ $booking->customer_phone }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-200">{{ $booking->movie?->title ?? 'Show' }}</td>
                            <td class="px-4 py-4 text-slate-200">{{ $booking->booking_time }}</td>
                            <td class="px-4 py-4 text-slate-200">{{ implode(', ', $booking->selected_seats ?? []) }}</td>
                            <td class="px-4 py-4 text-slate-200">LKR {{ number_format((float) $booking->total_amount, 2) }}</td>
                            <td class="px-4 py-4 capitalize text-slate-200">{{ $booking->payment_method }}</td>
                            <td class="px-4 py-4">
                                <p class="capitalize text-white">{{ $booking->payment_status }}</p>
                                @if ($booking->payment_status === 'cancelled')
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $booking->cancelledBy->name ?? 'Staff' }}
                                        @if ($booking->cancellation_reason)
                                            — {{ $booking->cancellation_reason }}
                                        @endif
                                    </p>
                                @endif
                                @if ($canCancel)
                                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="mt-3 flex flex-col gap-2">
                                        @csrf
                                        <label for="bookingManage__cancel_reason_{{ $booking->id }}" class="sr-only">Cancellation reason</label>
                                        <input id="bookingManage__cancel_reason_{{ $booking->id }}" name="cancellation_reason" type="text" required maxlength="255" placeholder="Reason" class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white">
                                        <button type="submit" class="rounded-full border border-rose-400/40 px-3 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-rose-200 hover:bg-rose-500/10">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">No bookings for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
