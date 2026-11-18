@props(['title' => 'Terms and Conditions'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Terms and Conditions</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-slate-400">Last updated: {{ config('cinema.business.policies_updated') }}</p>
            <p class="text-slate-300">
                These terms apply to ticket bookings made on this website for shows at {{ config('cinema.business.name') }}, {{ config('cinema.business.address') }}. By booking a ticket, you agree to these terms, our <a class="text-rose-300 hover:text-rose-200" href="{{ route('privacy') }}">Privacy Policy</a>, and our <a class="text-rose-300 hover:text-rose-200" href="{{ route('refund') }}">Refund Policy</a>.
            </p>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">1. Who can book</h2>
                <p class="text-slate-300">
                    You must be at least 18 years old to pay for a booking, or a parent or guardian must complete the payment for you. You must give your real name and a phone number we can use to reach you about the booking.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">2. What you are buying</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>A ticket is valid only for the film, date, time, and seat shown on your booking.</li>
                    <li>Each ticket admits one person. A box seat is sold as a pair where the booking page says so.</li>
                    <li>Tickets are collected at the cinema. We do not post or courier tickets. Please arrive at least 30 minutes before the show and bring your booking reference.</li>
                    <li>Seats are held only for the time shown during booking. A booking is confirmed after payment is received.</li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">3. Prices</h2>
                <p class="text-slate-300">
                    Ticket prices are shown in Sri Lankan Rupees (LKR) on the home page, the movies page, and again in the booking summary before you pay. The amount payable is the ticket price plus the handling fee displayed for that booking. The price confirmed on the payment page is the price you pay.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">4. Payment</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>Online card and wallet payments are processed by PayHere. {{ config('cinema.business.name') }} does not store your full card number.</li>
                    <li>You authorise PayHere to charge the amount shown as “Amount Payable” to the payment method you choose.</li>
                    <li>Your booking is confirmed when payment is successfully received.</li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">5. Changes, cancellation, and refunds</h2>
                <p class="text-slate-300">
                    You cannot exchange a paid ticket for another film, date, time, or seat. If you do not attend, the ticket is not refunded. If {{ config('cinema.business.name') }} cancels the show, or the show is cancelled because government permission is not granted, the amount you paid is refunded under our <a class="text-rose-300 hover:text-rose-200" href="{{ route('refund') }}">Refund Policy</a>. An approved refund is sent only to the same payment method you used to pay.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">6. At the cinema</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>Staff may carry out a security check at the entrance.</li>
                    <li>Persons under the influence of alcohol or other substances may be refused entry.</li>
                    <li>Reselling a ticket without our permission can lead to cancellation of that ticket without a refund.</li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">7. Your information</h2>
                <p class="text-slate-300">
                    We use the name, phone number, email, and NIC you enter to manage the booking. How we handle that information is described in the <a class="text-rose-300 hover:text-rose-200" href="{{ route('privacy') }}">Privacy Policy</a>.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">8. Liability</h2>
                <p class="text-slate-300">
                    {{ config('cinema.business.name') }} is not responsible for injury, theft, or loss of personal belongings at the venue, except where the law of Sri Lanka does not allow that responsibility to be excluded.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">9. Law</h2>
                <p class="text-slate-300">
                    These terms are governed by the laws of Sri Lanka. The courts of Sri Lanka have jurisdiction over disputes arising from a booking.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">10. Contact</h2>
                <p class="mb-3 text-slate-300">Questions about these terms can be sent to us using the details below.</p>
                @include('pages.partials.business-contact')
            </div>
        </div>
    </section>
</x-app-layout>
