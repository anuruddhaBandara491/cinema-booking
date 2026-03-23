<!-- Step 3: Payment -->
<div x-show="step === 3" x-cloak x-transition.opacity.duration.300>
    <div class="flex items-center justify-between mb-6">
        <h4 class="text-lg font-semibold text-white">Payment</h4>
        <!-- Timer Display for Payment Step -->
        <div x-show="getMaxLockTime() > 0" class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-900/50 to-amber-800/50 px-3 py-2 sm:px-4 sm:py-3 border border-amber-700/50">
            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-amber-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-[10px] sm:text-xs uppercase tracking-[0.2em] text-amber-400">Seat Lock</p>
                <p class="font-mono text-sm sm:text-base font-bold text-amber-100" x-text="formatTime(getMaxLockTime())"></p>
            </div>
        </div>
    </div>
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
