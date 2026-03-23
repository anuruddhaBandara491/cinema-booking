<!-- Step 3: Confirm Booking (Final Step) -->
<div x-show="step === 3" x-cloak x-transition.opacity.duration.300>
    <div class="flex items-center justify-between mb-8">
        <h4 class="text-lg font-semibold text-white">Confirm Your Booking</h4>
        <!-- Timer Display for Seat Lock -->
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

    <!-- Two-Column Layout: User Details (Left) + Booking Summary (Right) -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- LEFT SIDE: User Details Form -->
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
            <h5 class="text-base font-semibold text-white mb-4">Your Information</h5>

            <!-- Full Name Field -->
            <div class="mb-4">
                <label for="userName" class="block text-sm font-medium text-slate-200 mb-2">
                    Full Name <span class="text-rose-400">*</span>
                </label>
                <input
                    type="text"
                    id="userName"
                    x-model="userDetails.name"
                    placeholder="Enter your full name"
                    @input="validateStep3()"
                    class="w-full rounded-lg border border-white/10 bg-slate-900 px-4 py-2.5 text-white placeholder-slate-600 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                />
                <p x-show="showErrors && errors.name" x-text="errors.name" class="mt-1.5 text-xs text-rose-400"></p>
            </div>

            <!-- Phone Number Field -->
            <div class="mb-4">
                <label for="userPhone" class="block text-sm font-medium text-slate-200 mb-2">
                    Phone Number <span class="text-rose-400">*</span>
                </label>
                <input
                    type="tel"
                    id="userPhone"
                    x-model="userDetails.phoneNumber"
                    placeholder="Enter your phone number"
                    @input="validateStep3()"
                    class="w-full rounded-lg border border-white/10 bg-slate-900 px-4 py-2.5 text-white placeholder-slate-600 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                />
                <p x-show="showErrors && errors.phoneNumber" x-text="errors.phoneNumber" class="mt-1.5 text-xs text-rose-400"></p>
            </div>

            <!-- Email Field -->
            <div class="mb-6">
                <label for="userEmail" class="block text-sm font-medium text-slate-200 mb-2">
                    Email Address <span class="text-amber-300">*</span>
                </label>
                <input
                    type="email"
                    id="userEmail"
                    x-model="userDetails.email"
                    placeholder="Enter your email address"
                    @input="validateStep3()"
                    class="w-full rounded-lg border border-white/10 bg-slate-900 px-4 py-2.5 text-white placeholder-slate-600 transition focus:border-amber-300/60 focus:outline-none focus:ring-1 focus:ring-amber-300/40"
                />
                <p x-show="showErrors && errors.email" x-text="errors.email" class="mt-1.5 text-xs text-rose-400"></p>
                <p x-show="!errors.email && userDetails.email.trim() !== ''" class="mt-1.5 text-xs text-slate-500">Email confirmed</p>
            </div>
        </div>

        <!-- RIGHT SIDE: Booking Summary -->
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
            <h5 class="text-base font-semibold text-white mb-4">Booking Summary</h5>
            <div class="space-y-4 text-sm">
                <!-- Movie Name -->
                <div class="flex justify-between text-slate-300">
                    <span class="text-slate-400">Movie:</span>
                    <span class="text-white font-medium" x-text="movieName"></span>
                </div>

                <!-- Date & Time -->
                <div class="flex justify-between text-slate-300">
                    <span class="text-slate-400">Date & Time:</span>
                    <span class="text-white font-medium"><span x-text="selectedDate"></span> at <span x-text="selectedTime"></span></span>
                </div>

                <!-- Tickets -->
                <div class="flex justify-between text-slate-300">
                    <span class="text-slate-400">Tickets:</span>
                    <span class="text-white font-medium" x-text="totalTickets()"></span>
                </div>

                <!-- Selected Seats -->
                <div class="border-t border-slate-700 pt-3">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.1em] mb-2">Selected Seats</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="seat in seats" :key="seat">
                            <span class="inline-block rounded bg-emerald-500/20 border border-emerald-500/50 px-3 py-1 text-xs font-medium text-emerald-100" x-text="seat"></span>
                        </template>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="border-t border-slate-700 pt-3 space-y-2">
                    <p class="text-slate-400 text-xs uppercase tracking-[0.1em]">Price Breakdown</p>
                    <div class="flex justify-between text-slate-400 text-xs">
                        <span>Seat Price:</span>
                        <span class="text-slate-300" x-text="`LKR ${calculateSeatPrices()?.toLocaleString() || '0'}`"></span>
                    </div>
                    <div class="flex justify-between text-slate-400 text-xs">
                        <span>Booking Charge:</span>
                        <span class="text-slate-300" x-text="`LKR ${calculateBookingCharge()?.toLocaleString() || '0'}`"></span>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="border-t border-slate-700 pt-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Total Amount:</span>
                        <span class="text-lg font-bold text-amber-300" x-text="`LKR ${calculateTotal()?.toLocaleString() || '0'}`"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
