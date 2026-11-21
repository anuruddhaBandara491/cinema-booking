<!-- Step 4: PayHere payment -->
<div x-show="step === 4" x-cloak x-transition.opacity.duration.300>
    <h4 class="text-lg font-semibold text-white">Pay with PayHere</h4>
    <p class="mt-2 text-sm text-slate-400">
        Review the amount, then continue. Card details are entered on PayHere, not on this site.
    </p>

    @if ($payhereSandbox)
        <div class="mt-4 rounded-xl border border-amber-400/30 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">
            <p>Sandbox mode is on. No real charge is made. On the PayHere page, use one of these test cards. Any name, CVV, and future expiry date will work.</p>
            <ul class="mt-2 space-y-1">
                <li>Visa 4916 2175 0161 1292</li>
                <li>MasterCard 5307 7321 2553 1191</li>
                <li>AMEX 3467 8100 5510 225</li>
            </ul>
        </div>
    @endif

    @if ($payhereNotifyIsLocal)
        <p class="mt-4 rounded-xl border border-white/10 bg-canvas-muted px-4 py-3 text-sm text-slate-300">
            This site is on localhost. PayHere can take the test payment, but it cannot call back this computer.
            The booking stays pending until the notify URL is a public address.
        </p>
    @endif

    <div id="bookingPayment__summary" class="mt-6 rounded-2xl border border-white/10 bg-canvas-muted p-6">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Order</p>
        <div class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <span class="text-slate-400">Movie</span>
                <span class="text-right text-white" x-text="movieName"></span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-400">Date and time</span>
                <span class="text-right text-white"><span x-text="selectedDate"></span> at <span x-text="selectedTime"></span></span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-400">Seats</span>
                <span class="text-right text-white" x-text="seats.join(', ') || 'None'"></span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-400">Name</span>
                <span class="text-right text-white" x-text="userDetails.name"></span>
            </div>
            <div class="flex justify-between gap-4">
                <span class="text-slate-400">Email</span>
                <span class="text-right text-white" x-text="userDetails.email"></span>
            </div>
            <div class="flex justify-between gap-4 border-t border-white/10 pt-3">
                <span class="font-semibold text-white">Amount payable</span>
                <span class="text-lg font-bold text-amber-300" x-text="`LKR ${calculateTotal()}`"></span>
            </div>
        </div>
    </div>
</div>
