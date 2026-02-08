<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Profile</p>
            <h2 class="text-2xl font-semibold text-white">Account settings</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card-surface p-6 lg:col-span-2">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="space-y-6">
                <div class="card-surface p-6">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="card-surface p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
