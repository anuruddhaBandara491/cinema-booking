@php($title = 'Reset Password')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="card-surface w-full p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Set new password</p>
            <h1 class="text-2xl font-semibold text-white">Secure your account</h1>
            <p class="mt-2 text-sm text-slate-400">Choose a strong password to continue.</p>

            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="username"
                           value="{{ old('email', $request->email) }}"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                    @error('email')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                    @error('password')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-xs uppercase tracking-[0.2em] text-slate-400">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                    @error('password_confirmation')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn-primary w-full">Reset password</button>
            </form>
        </div>
    </section>
</x-app-layout>
