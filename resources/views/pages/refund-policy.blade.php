@props(['title' => 'Refund Policy'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Refund Policy</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-slate-400">Last updated: {{ config('cinema.business.policies_updated') }}</p>
            <p class="text-slate-300">
                This policy explains when {{ config('cinema.business.name') }} refunds a ticket bought on this website, and how that money is returned.
            </p>

            <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 p-4">
                <p class="font-semibold text-amber-200">Refunds go back to the original payment method</p>
                <p class="mt-2 text-slate-200">
                    If a refund is approved, it is paid back to the same payment method that was used to make the original payment (the payment-initiated medium). We do not send a refund to a different card, bank account, wallet, or as cash.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">When we refund</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>{{ config('cinema.business.name') }} cancels the show.</li>
                    <li>The show is cancelled because the required government permission is not granted.</li>
                </ul>
                <p class="mt-3 text-slate-300">
                    In those cases we refund the full amount you paid online for that booking, including the ticket price and the handling fee charged on the same payment. The refund is started to your original payment method within 7 business days of the cancellation. Your bank or PayHere may take further time before the amount appears on that card, account, or wallet.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">When we do not refund</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>You change your mind after payment.</li>
                    <li>You do not attend, or you arrive after the show has started.</li>
                    <li>You want a different film, date, time, or seat. Paid tickets are not exchanged.</li>
                    <li>The ticket is cancelled because it was resold without our permission.</li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">How you receive the ticket</h2>
                <p class="text-slate-300">
                    Tickets are collected at the cinema counter. Please arrive at least 30 minutes before the show with your booking reference. We do not deliver tickets by post or courier, so there is no return shipment.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Contact</h2>
                <p class="mb-3 text-slate-300">
                    If your show is cancelled and you have not received the refund on your original payment method, contact us with your booking reference and the phone number used for the booking.
                </p>
                @include('pages.partials.business-contact')
            </div>
        </div>
    </section>
</x-app-layout>
