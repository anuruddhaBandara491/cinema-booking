<div class="rounded-lg border border-white/10 bg-slate-900 p-4 text-slate-200">
    <p><strong>Cinema:</strong> {{ config('cinema.business.name') }}</p>
    <p><strong>Address:</strong> {{ config('cinema.business.address') }}</p>
    <p><strong>Phone:</strong> <a class="text-rose-300 hover:text-rose-200" href="tel:{{ preg_replace('/\s+/', '', config('cinema.business.phone')) }}">{{ config('cinema.business.phone') }}</a></p>
    <p><strong>Email:</strong> <a class="text-rose-300 hover:text-rose-200" href="mailto:{{ config('cinema.business.email') }}">{{ config('cinema.business.email') }}</a></p>
</div>
