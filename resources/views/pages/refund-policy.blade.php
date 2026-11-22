@props(['title' => 'Refund Policy'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Refund Policy</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-slate-400">Last updated: {{ config('cinema.business.policies_updated') }}</p>
            <p class="text-slate-300">
                Tickets bought on this website for shows at {{ config('cinema.business.name') }} are sold as final. The amount you pay is not refunded, and the seat number on a paid booking cannot be changed.
            </p>

            <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 p-4">
                <p class="font-semibold text-amber-200">No refund of the ticket amount</p>
                <p class="mt-2 text-slate-200">
                    {{ config('cinema.business.name') }} does not refund the ticket price or the handling fee. The amount is not returned in cash, and it is not sent back to a card, bank account, or wallet.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Seat numbers</h2>
                <p class="text-slate-300">
                    Check the seat numbers before you pay. After payment, the seat number cannot be changed, and the ticket cannot be moved to another film, date, or time.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">How you receive the ticket</h2>
                <p class="text-slate-300">
                    Tickets are collected at the cinema counter. Please arrive at least 30 minutes before the show with your booking reference. We do not deliver tickets by post or courier.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Contact</h2>
                <p class="mb-3 text-slate-300">
                    Questions about a booking can be sent to us with the booking reference and the phone number used for the booking. Contacting us does not create a refund or a seat change.
                </p>
                @include('pages.partials.business-contact')
            </div>
        </div>
    </section>
</x-app-layout>
