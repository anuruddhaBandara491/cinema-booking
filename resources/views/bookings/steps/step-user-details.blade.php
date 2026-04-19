<!-- Step 4: User Details -->
<div x-show="step === 4" x-cloak x-transition.opacity.duration.300>
    <h4 class="text-lg font-semibold text-white">Your Details</h4>
    <p class="mt-2 text-sm text-slate-400">Please provide your contact information</p>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <!-- Name Field (Mandatory) -->
        <div>
            <label for="fullName" class="block text-sm font-medium text-slate-200">
                Full Name <span class="text-rose-400">*</span>
            </label>
            <input
                type="text"
                id="fullName"
                x-model="userDetails.name"
                placeholder="Enter your full name"
                class="mt-2 w-full rounded-lg border border-white/10 bg-canvas-muted px-4 py-2 text-white placeholder-slate-500 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                required
            />
            <p x-show="showErrors && errors.name" x-text="errors.name" class="mt-1 text-xs text-rose-400"></p>
            <p x-show="!showErrors || !errors.name" class="mt-1 text-xs text-slate-500">Required field</p>
        </div>

        <!-- Phone Number Field (Mandatory) -->
        <div>
            <label for="phoneNumber" class="block text-sm font-medium text-slate-200">
                Phone Number <span class="text-rose-400">*</span>
            </label>
            <input
                type="tel"
                id="phoneNumber"
                x-model="userDetails.phoneNumber"
                placeholder="Enter your phone number"
                pattern="[0-9\-\+\s\(\)]+"
                class="mt-2 w-full rounded-lg border border-white/10 bg-canvas-muted px-4 py-2 text-white placeholder-slate-500 transition focus:border-rose-400/60 focus:outline-none focus:ring-1 focus:ring-rose-400/40"
                required
            />
            <p x-show="showErrors && errors.phoneNumber" x-text="errors.phoneNumber" class="mt-1 text-xs text-rose-400"></p>
            <p x-show="!showErrors || !errors.phoneNumber" class="mt-1 text-xs text-slate-500">Required field</p>
        </div>

        <!-- Email Field (Optional) -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-200">
                Email Address
            </label>
            <input
                type="email"
                id="email"
                x-model="userDetails.email"
                placeholder="Enter your email (optional)"
                class="mt-2 w-full rounded-lg border border-white/10 bg-canvas-muted px-4 py-2 text-white placeholder-slate-500 transition focus:border-amber-300/60 focus:outline-none focus:ring-1 focus:ring-amber-300/40"
            />
            <p class="mt-1 text-xs text-slate-500">Optional field</p>
        </div>

        <!-- NIC Field (Optional) -->
        <div>
            <label for="nic" class="block text-sm font-medium text-slate-200">
                NIC / ID Number
            </label>
            <input
                type="text"
                id="nic"
                x-model="userDetails.nic"
                placeholder="Enter your NIC or ID (optional)"
                class="mt-2 w-full rounded-lg border border-white/10 bg-canvas-muted px-4 py-2 text-white placeholder-slate-500 transition focus:border-amber-300/60 focus:outline-none focus:ring-1 focus:ring-amber-300/40"
            />
            <p class="mt-1 text-xs text-slate-500">Optional field</p>
        </div>
    </div>

    <!-- Summary of entered details -->
    <div class="mt-6 rounded-2xl border border-white/10 bg-canvas-muted p-5">
        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Summary</p>
        <div class="mt-3 space-y-2 text-sm text-slate-300">
            <p><span class="text-white">Name:</span> <span x-text="userDetails.name || 'Not provided'"></span></p>
            <p><span class="text-white">Phone:</span> <span x-text="userDetails.phoneNumber || 'Not provided'"></span></p>
            <p><span class="text-white">Email:</span> <span x-text="userDetails.email || 'Not provided'"></span></p>
            <p><span class="text-white">NIC/ID:</span> <span x-text="userDetails.nic || 'Not provided'"></span></p>
        </div>
    </div>
</div>
