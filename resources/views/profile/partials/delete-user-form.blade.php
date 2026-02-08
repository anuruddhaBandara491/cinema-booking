<section class="space-y-4">
    <header>
        <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Danger zone</p>
        <h2 class="text-xl font-semibold text-white">Delete account</h2>
        <p class="mt-2 text-sm text-slate-400">
            Deleting your account is permanent. Please save any data you want to keep.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="w-full rounded-full border border-rose-500/30 bg-rose-500/10 px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-rose-200 hover:border-rose-500/60"
    >Delete account</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-white">Are you sure you want to delete your account?</h2>
            <p class="mt-2 text-sm text-slate-400">
                This action cannot be undone. Enter your password to confirm.
            </p>

            <div class="mt-6">
                <label for="password" class="text-xs uppercase tracking-[0.2em] text-slate-400">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Password"
                    class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-rose-500/60"
                />

                @if ($errors->userDeletion->has('password'))
                    <p class="mt-2 text-xs text-rose-300">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="btn-ghost">Cancel</button>
                <button class="rounded-full bg-rose-500 px-6 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-white hover:bg-rose-400">
                    Delete account
                </button>
            </div>
        </form>
    </x-modal>
</section>
