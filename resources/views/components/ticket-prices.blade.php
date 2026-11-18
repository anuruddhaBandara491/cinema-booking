@props(['ticketTypes'])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-white/10 bg-canvas-muted p-6']) }}>
    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Prices</p>
    <h2 class="mt-2 text-2xl font-semibold text-white">Ticket prices</h2>
    <p class="mt-2 text-sm leading-6 text-slate-300">
        All prices are in Sri Lankan Rupees (LKR). The price you pay is the ticket price below plus the handling fee shown on the film. The full amount is shown again before you pay.
    </p>

    @if ($ticketTypes->isEmpty())
        <p class="mt-4 text-sm text-slate-400">Ticket prices are being updated. Please check back before booking.</p>
    @else
        <div class="mt-6 overflow-hidden rounded-xl border border-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900/80 text-xs uppercase tracking-[0.15em] text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Ticket</th>
                        <th class="px-4 py-3 font-medium">Adult</th>
                        <th class="px-4 py-3 font-medium">Child</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($ticketTypes as $ticketType)
                        <tr>
                            <td class="px-4 py-3 font-medium text-white">{{ $ticketType->name }}</td>
                            <td class="px-4 py-3 text-slate-200">LKR {{ number_format($ticketType->adult_price, 2) }}</td>
                            <td class="px-4 py-3 text-slate-200">
                                @if ($ticketType->has_child && $ticketType->child_price !== null)
                                    LKR {{ number_format($ticketType->child_price, 2) }}
                                @else
                                    Not offered
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="mt-4 text-sm text-slate-400">
        Read our
        <a class="text-rose-300 hover:text-rose-200" href="{{ route('terms') }}">Terms & Conditions</a>,
        <a class="text-rose-300 hover:text-rose-200" href="{{ route('privacy') }}">Privacy Policy</a>, and
        <a class="text-rose-300 hover:text-rose-200" href="{{ route('refund') }}">Refund Policy</a>.
    </p>
</section>
