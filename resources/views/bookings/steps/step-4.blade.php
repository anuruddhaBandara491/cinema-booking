<!-- Step 4 -->
<div x-show="step === 4" x-transition.opacity.duration.300>
    <h4 class="text-lg font-semibold text-white">Payment</h4>
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Summary</p>
            <div class="mt-3 space-y-2 text-sm text-slate-300">
                <p><span class="text-white">Date:</span> <span x-text="selectedDate"></span></p>
                <p><span class="text-white">Time:</span> <span x-text="selectedTime"></span></p>
                <p><span class="text-white">Tickets:</span> <span x-text="totalTickets()"></span></p>
                <p><span class="text-white">Seats:</span> <span x-text="seats.join(', ') || 'None'"></span></p>
            </div>
        </div>
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Payment Method</p>
            <div class="mt-3 space-y-3">
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="radio" name="payment" value="card" class="h-4 w-4" checked @click="paymentMethod = 'card'" />
                    Card payment
                </label>
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="radio" name="payment" value="wallet" class="h-4 w-4" @click="paymentMethod = 'wallet'" />
                    Wallet
                </label>
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="radio" name="payment" value="cash" class="h-4 w-4" @click="paymentMethod = 'cash'" />
                    Pay at counter
                </label>
            </div>
            <button type="button" class="btn-primary mt-6 w-full">Confirm payment</button>
        </div>
    </div>
</div>
