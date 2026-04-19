<section>
    <header>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Profile information</p>
        <h2 class="text-xl font-semibold text-white">Update your details</h2>
        <p class="mt-2 text-sm text-slate-400">Keep your name and email up to date.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="text-xs uppercase tracking-[0.2em] text-slate-400">Name</label>
            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                   value="{{ old('name', $user->name) }}"
                   class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
            @error('name')
                <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email', $user->email) }}"
                   class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
            @error('email')
                <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-400/20 bg-amber-500/10 px-4 py-3">
                    <p class="text-sm text-amber-200">
                        Your email address is unverified.
                        <button form="send-verification" class="ml-2 text-xs uppercase tracking-[0.2em] text-amber-100 hover:text-white">
                            Re-send verification
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-200">A new verification link has been sent.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button class="btn-primary">Save</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-xs text-emerald-200"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
