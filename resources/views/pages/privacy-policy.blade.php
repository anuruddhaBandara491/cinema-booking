@props(['title' => 'Privacy Policy'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Privacy Policy</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-slate-400">Last updated: {{ config('cinema.business.policies_updated') }}</p>
            <p class="text-slate-300">
                {{ config('cinema.business.name') }} (“we”) explains here how we handle personal information when you use this website to book a cinema ticket. By booking, you agree to this policy.
            </p>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Information we collect</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>Name, phone number, email address, and NIC, which you type in during booking.</li>
                    <li>The film, show date, show time, seats, ticket types, and amount of the booking.</li>
                    <li>A booking reference created when the booking is saved.</li>
                    <li>Technical information needed to run the site, such as the session used to hold your selected seats and standard server logs (including IP address and browser type).</li>
                </ul>
                <p class="mt-3 text-slate-300">
                    Card numbers, card expiry dates, and card security codes are entered on PayHere’s payment page. We do not receive or store your full card details. PayHere processes that payment information under its own privacy policy.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">How we use it</h2>
                <ul class="list-disc space-y-2 pl-5 text-slate-300">
                    <li>To take the booking, reserve the seats, and confirm payment.</li>
                    <li>To contact you about that booking, including a cancelled show.</li>
                    <li>To admit you at the cinema when you present the booking.</li>
                    <li>To keep records we need for accounts, disputes, and the law of Sri Lanka.</li>
                </ul>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Who we share it with</h2>
                <p class="text-slate-300">
                    We do not sell your personal information. We share booking and payment details with PayHere so the payment can be processed. We may also disclose information if a Sri Lankan law, court, or regulator requires it.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Cookies</h2>
                <p class="text-slate-300">
                    The site uses a session cookie so your booking steps and temporary seat hold stay connected. You can block cookies in your browser. Seat selection and checkout will not work without that session.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">How long we keep it</h2>
                <p class="text-slate-300">
                    We keep booking records for as long as we need them to run the cinema and meet accounting and legal duties. Seat holds that are not paid expire automatically and are removed.
                </p>
            </div>

            <div>
                <h2 class="mb-3 text-2xl font-bold text-white">Contact</h2>
                <p class="mb-3 text-slate-300">
                    To ask what information we hold about a booking, or to ask us to correct a name or phone number, contact us below. Please include the booking reference.
                </p>
                @include('pages.partials.business-contact')
            </div>
        </div>
    </section>
</x-app-layout>
