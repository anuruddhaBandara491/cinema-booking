@php($title = 'Login')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-6xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid w-full gap-10 lg:grid-cols-2 lg:items-center">
            <div class="space-y-4">
                <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Welcome back</p>
                <h1 class="text-4xl font-semibold text-white">Your next screening awaits.</h1>
                <p class="text-sm text-slate-300">Sign in to manage bookings, reserve seats, and access member perks.</p>
            </div>

            <div class="card-surface p-8">
                <h2 class="text-xl font-semibold text-white">Login</h2>
                <p class="text-sm text-slate-400">Enter your details to continue.</p>

                @if (session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.login.submit') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
                        <input id="email" name="email" type="email" autocomplete="username" required autofocus
                               value="{{ old('email') }}"
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('email')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('password')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center gap-2 text-xs text-slate-400">
                            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-white/20 bg-slate-900 text-rose-400 focus:ring-rose-500/60" />
                            Remember me
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs text-rose-300 hover:text-rose-200" href="{{ route('password.request') }}">Forgot password?</a>
                        @endif
                    </div>

                    <button class="btn-primary w-full">Log in</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
