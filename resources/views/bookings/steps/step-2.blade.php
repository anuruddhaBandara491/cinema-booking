<!-- Step 2 -->
<div x-show="step === 2" x-cloak x-transition.opacity.duration.300>
    <h4 class="text-lg font-semibold text-white">Select Tickets</h4>
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        @forelse ($ticketTypes as $ticketType)
            <div class="rounded-2xl border border-white/10 bg-canvas-muted px-5 py-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-400">{{ $ticketType->name }}</p>
                    @if (strtolower($ticketType->name) === 'box')
                        <p class="mt-2 text-xs uppercase tracking-[0.2em] text-amber-300">Box seats must be selected in pairs</p>
                    @endif
                </div>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Adult</p>
                            <p class="text-lg font-semibold text-white">LKR {{ number_format($ticketType->adult_price, 2) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                    @click="decrementTicket({{ $ticketType->id }}, 'adult')">-</button>
                            <span class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].adult"></span>
                            <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                    @click="incrementTicket({{ $ticketType->id }}, 'adult')">+</button>
                        </div>
                    </div>

                    @if ($ticketType->has_child)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Child</p>
                                <p class="text-lg font-semibold text-white">LKR {{ number_format($ticketType->child_price, 2) }}</p>
                                @if (strtolower($ticketType->name) === 'odc')
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-slate-500">Child seats allowed ages 2-13 only</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                        @click="decrementTicket({{ $ticketType->id }}, 'child')">-</button>
                                <span class="min-w-[2ch] text-center text-white" x-text="tickets[{{ $ticketType->id }}].child"></span>
                                <button type="button" class="h-8 w-8 rounded-full border border-white/10 text-white"
                                        @click="incrementTicket({{ $ticketType->id }}, 'child')">+</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-white/10 bg-canvas-muted p-6 text-center text-sm text-slate-400">
                No ticket types configured yet.
            </div>
        @endforelse
    </div>
</div>
