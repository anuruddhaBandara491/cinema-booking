<div>
    @isset($header)
        <header class="border-b border-white/10 bg-slate-950/60">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="flex-1">
        {{ $slot }}
    </main>
</div>
