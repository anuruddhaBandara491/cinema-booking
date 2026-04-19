@php($title = 'Verify Email')

<x-app-layout>
    <section class="mx-auto flex min-h-[70vh] max-w-3xl items-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="card-surface w-full p-8">
            <p class="text-xs uppercase tracking-[0.3em] text-rose-300">Verify email</p>
            <h1 class="text-2xl font-semibold text-white">Confirm your email address</h1>
            <p class="mt-2 text-sm text-slate-400">
                Thanks for signing up. Please verify your email by clicking the link we sent.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    A new verification link has been sent to your email address.
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button class="btn-primary">Resend verification email</button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">Log out</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
