@php($title = 'Confirm Password')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="card-surface w-full p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Security check</p>
            <h1 class="text-2xl font-semibold text-white">Confirm your password</h1>
            <p class="mt-2 text-sm text-slate-400">Please confirm your password before continuing.</p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                    @error('password')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn-primary w-full">Confirm</button>
            </form>
        </div>
    </section>
</x-app-layout>
