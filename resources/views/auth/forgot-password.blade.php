@php($title = 'Reset Password')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="card-surface w-full p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Password reset</p>
            <h1 class="text-2xl font-semibold text-white">Restore access</h1>
            <p class="mt-2 text-sm text-slate-400">Share your email and we will send a reset link.</p>

            @if (session('status'))
                <div class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="text-xs uppercase tracking-[0.2em] text-slate-400">Email</label>
                    <input id="email" name="email" type="email" required autofocus
                           value="{{ old('email') }}"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60" />
                    @error('email')
                        <p class="mt-2 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <button class="btn-primary w-full">Email reset link</button>
            </form>
        </div>
    </section>
</x-app-layout>
