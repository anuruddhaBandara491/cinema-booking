<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Payment</p>
            <h2 class="text-2xl font-semibold text-white">Pay with PayHere</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6 text-center">
            @if ($sandbox)
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Sandbox</p>
            @endif

            <h3 class="mt-3 text-xl font-semibold text-white">{{ $booking->movie->title }}</h3>
            <p class="mt-2 text-sm text-slate-300">
                {{ $booking->booking_date->format('D, M d, Y') }} at {{ $booking->booking_time }}
            </p>
            <p class="mt-6 text-sm text-slate-400">Amount payable</p>
            <p class="text-3xl font-semibold text-white">LKR {{ number_format($booking->total_amount, 2) }}</p>
            <p class="mt-2 font-mono text-sm text-slate-400">{{ $booking->booking_reference }}</p>

            @if ($checkoutUrl)
                <p class="mt-6 text-sm text-slate-300">Redirecting you to PayHere to enter your card details.</p>
                <form id="bookingPayment__payhere_form" method="post" action="{{ $checkoutUrl }}" class="mt-6">
                    @foreach ($fields as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="btn-primary w-full">Continue to PayHere</button>
                </form>
                <script>
                    document.getElementById('bookingPayment__payhere_form').submit();
                </script>
            @else
                <p class="mt-6 text-sm text-rose-200">
                    PayHere is not configured yet. Add PAYHERE_MERCHANT_ID and PAYHERE_MERCHANT_SECRET to the environment file, then open this payment page again.
                </p>
                <a href="{{ route('movies.index') }}" class="btn-ghost mt-6 inline-block">Back to movies</a>
            @endif
        </div>
    </section>
</x-app-layout>
