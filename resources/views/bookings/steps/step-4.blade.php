<!-- Step 3: Confirm Booking (Final Step) -->
<div x-show="step === 3" x-cloak x-transition.opacity.duration.300>
    <div class="flex items-center justify-between mb-8">
        <h4 class="text-lg font-semibold text-white">Confirm Your Booking</h4>
        <!-- Timer Display for Seat Lock -->

    </div>

    <!-- Two-Column Layout: Disclaimer (Left) + Booking Summary (Right) -->
    <div class="grid gap-6 lg:grid-cols-2 mb-8">
        <!-- LEFT SIDE: Disclaimer Section -->
        <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6 max-h-150 overflow-y-auto">
            <h5 class="text-base font-semibold text-white mb-4">Disclaimer</h5>
            <div class="space-y-2 text-xs leading-7 text-slate-300">
                <p><span class="font-semibold text-slate-200">1.</span> Tickets once booked cannot be exchanged or refunded.</p>
                <p><span class="font-semibold text-slate-200">2.</span> No refund on a purchased ticket is possible, even in case of any rescheduling.</p>
                <p><span class="font-semibold text-slate-200">3.</span> Ticket purchased is valid only for the particular show & cannot be exchanged or used for other shows/cities.</p>
                <p><span class="font-semibold text-slate-200">4.</span> We recommend that you arrive at least 30 minutes prior at the venue to pick up your physical tickets.</p>
                <p><span class="font-semibold text-slate-200">5.</span> The event is subject to government permissions. In case the permissions are not granted and event is canceled, a refund shall be issued to all patrons.</p>
                <p><span class="font-semibold text-slate-200">6.</span> Unlawful resale (or attempted unlawful resale) of a ticket would lead to seizure & cancellation of that ticket without refund or other compensation & deemed action will be taken against such parties.</p>
                <p><span class="font-semibold text-slate-200">7.</span> Each ticket admits one person only.</p>
                <p><span class="font-semibold text-slate-200">8.</span> Internet handling fee per ticket may be levied.</p>
                <p><span class="font-semibold text-slate-200">9.</span> Organizers reserve the right to perform security checks on invitees/members of the audience at the entry point for security reasons.</p>
                <p><span class="font-semibold text-slate-200">10.</span> Persons under the influence of alcohol or any substances will not be allowed inside the venue, any kind of disrespect or harm to Actors & crew will not be tolerated.</p>
                <p><span class="font-semibold text-slate-200">11.</span> Organizers or any of its agents, officers, employees shall not be responsible for any injury, damage, theft, losses or cost suffered at or as a result of the event or any part of it.</p>
            </div>
        </div>

        <!-- RIGHT SIDE: Booking Summary -->
        <div class="flex flex-col gap-4">
            <!-- Booking Summary Card -->
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-6">
                <h5 class="text-base font-semibold text-white mb-4">Booking Summary</h5>
                <div class="space-y-4 text-sm">
                    <!-- User Details Form -->
                    <div class="space-y-3">
                        <div>
                            <label for="userName" class="block text-xs font-medium text-slate-200 mb-1">Full Name <span class="text-rose-400">*</span></label>
                            <input
                                type="text"
                                id="userName"
                                x-model="userDetails.name"
                                placeholder="Enter your full name"
                                @input="validateStep3()"
                                class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-white text-sm placeholder-slate-600 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                            />
                            <p x-show="showErrors && errors.name" x-text="errors.name" class="mt-1 text-xs text-rose-400"></p>
                        </div>
                        <div>
                            <label for="userPhone" class="block text-xs font-medium text-slate-200 mb-1">Phone Number <span class="text-rose-400">*</span></label>
                            <input
                                type="tel"
                                id="userPhone"
                                x-model="userDetails.phoneNumber"
                                placeholder="Enter your phone number"
                                @input="validateStep3()"
                                class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-white text-sm placeholder-slate-600 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                            />
                            <p x-show="showErrors && errors.phoneNumber" x-text="errors.phoneNumber" class="mt-1 text-xs text-rose-400"></p>
                        </div>
                        <div>
                            <label for="userEmail" class="block text-xs font-medium text-slate-200 mb-1">Email Address <span class="text-amber-300">*</span></label>
                            <input
                                type="email"
                                id="userEmail"
                                x-model="userDetails.email"
                                placeholder="Enter your email address"
                                @input="validateStep3()"
                                class="w-full rounded-lg border border-white/10 bg-slate-900 px-3 py-2 text-white text-sm placeholder-slate-600 transition focus:border-amber-300/60 focus:outline-none focus:ring-1 focus:ring-amber-300/40"
                            />
                            <p x-show="showErrors && errors.email" x-text="errors.email" class="mt-1 text-xs text-rose-400"></p>
                        </div>
                    </div>

                    <!-- Movie Info -->
                    <div class="border-t border-slate-700 pt-3 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Movie:</span>
                            <span class="text-white font-medium" x-text="movieName"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Date & Time:</span>
                            <span class="text-white font-medium"><span x-text="selectedDate"></span> at <span x-text="selectedTime"></span></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Seats:</span>
                            <span class="text-white font-medium" x-text="seats.join(', ')"></span>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="border-t border-slate-700 pt-3 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Ticket Price:</span>
                            <span class="text-slate-300" x-text="`LKR ${calculateSeatPrices()?.toLocaleString() || '0'}`"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Handling Fees:</span>
                            <span class="text-slate-300" x-text="`LKR ${calculateBookingCharge()?.toLocaleString() || '0'}`"></span>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span class="text-slate-300">Sub total</span>
                            <span class="text-white" x-text="`LKR ${calculateTotal()?.toLocaleString() || '0'}`"></span>
                        </div>
                    </div>

                    <!-- Amount Payable (highlighted) -->
                    <div class="border-t border-slate-700 pt-3 bg-amber-500/10 border border-amber-500/20 rounded-lg p-3">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-white">Amount Payable</span>
                            <span class="text-lg font-bold text-amber-300" x-text="`LKR ${calculateTotal()?.toLocaleString() || '0'}`"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Verification Checkboxes -->
            <div class="space-y-3">
                <!-- Terms Checkbox -->
                <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-white/10 bg-canvas-muted p-4 hover:border-white/20 transition">
                    <input
                        type="checkbox"
                        x-model="termsAccepted"
                        class="h-5 w-5 mt-0.5 rounded border-white/20 bg-slate-900 text-rose-500 focus:ring-2 focus:ring-rose-400 transition"
                    />
                    <span class="text-xs text-slate-300 leading-relaxed">
                        I agree to the <span class="text-white font-semibold">Terms and Conditions</span>
                    </span>
                </label>

                <!-- Verification Checkbox -->
                <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-white/10 bg-canvas-muted p-4 hover:border-white/20 transition">
                    <input
                        type="checkbox"
                        x-model="verificationAccepted"
                        class="h-5 w-5 mt-0.5 rounded border-white/20 bg-slate-900 text-rose-500 focus:ring-2 focus:ring-rose-400 transition"
                    />
                    <span class="text-xs text-slate-300 leading-relaxed">
                        I have verified the cinema name, show date and time before proceeding to payment. Once booked cinema does not allow us to <span class="text-white font-semibold">Refund/Modify</span> the booking.
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
