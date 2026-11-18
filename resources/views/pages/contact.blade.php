@props(['title' => 'Contact'])

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-white">Contact</h1>
    </x-slot>

    <section class="bg-slate-950 py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <p class="text-slate-300">
                {{ config('cinema.business.name') }} sells cinema tickets for shows at our cinema in Monaragala. For a booking, a cancelled show, or a refund, contact us with your booking reference.
            </p>
            @include('pages.partials.business-contact')
            <p class="text-sm text-slate-400">
                <a class="text-rose-300 hover:text-rose-200" href="{{ route('terms') }}">Terms & Conditions</a>
                <span class="px-2 text-slate-600">·</span>
                <a class="text-rose-300 hover:text-rose-200" href="{{ route('privacy') }}">Privacy Policy</a>
                <span class="px-2 text-slate-600">·</span>
                <a class="text-rose-300 hover:text-rose-200" href="{{ route('refund') }}">Refund Policy</a>
            </p>
        </div>
    </section>
</x-app-layout>
