<section>
    <header>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Security</p>
        <h2 class="text-xl font-semibold text-white">Update password</h2>
        <p class="mt-2 text-sm text-slate-400">Use a strong password to keep your account safe.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Current password</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
            @if ($errors->updatePassword->has('current_password'))
                <p class="mt-2 text-xs text-rose-300">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="text-xs uppercase tracking-[0.2em] text-slate-400">New password</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
            @if ($errors->updatePassword->has('password'))
                <p class="mt-2 text-xs text-rose-300">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-xs uppercase tracking-[0.2em] text-slate-400">Confirm password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
            @if ($errors->updatePassword->has('password_confirmation'))
                <p class="mt-2 text-xs text-rose-300">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button class="btn-primary">Save</button>

            @if (session('status') === 'password-updated')
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
