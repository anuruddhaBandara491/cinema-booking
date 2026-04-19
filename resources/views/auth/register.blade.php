@php($title = 'Register')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-6xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid w-full gap-10 lg:grid-cols-2 lg:items-center">
            <div class="space-y-4">
                <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Create account</p>
                <h1 class="text-4xl font-semibold text-white">Join the cinema experience.</h1>
                <p class="text-sm text-slate-300">Manage tickets, save favorites, and unlock member rewards.</p>
            </div>

            <div class="card-surface p-8">
                <h2 class="text-xl font-semibold text-white">Register</h2>
                <p class="text-sm text-slate-400">Set up your profile to get started.</p>

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="text-xs uppercase tracking-[0.2em] text-slate-400">Name</label>
                        <input id="name" name="name" type="text" autocomplete="name" required autofocus
                               value="{{ old('name') }}"
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('name')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
                        <input id="email" name="email" type="email" autocomplete="username" required
                               value="{{ old('email') }}"
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('email')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('password')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs uppercase tracking-[0.2em] text-slate-400">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                        @error('password_confirmation')
                            <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <a href="{{ route('auth.login') }}" class="text-rose-300 hover:text-rose-200">Already registered?</a>
                    </div>

                    <button class="btn-primary w-full">Register</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
