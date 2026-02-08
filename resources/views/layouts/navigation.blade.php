@php
    $user = Auth::user();
    $hasRole = function (?string $role) use ($user) {
        if (! $user || ! $role) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        if (method_exists($user, 'getRoleNames')) {
            return $user->getRoleNames()->map(fn ($name) => strtolower($name))->contains(strtolower($role));
        }

        $value = $user->role ?? $user->role_name ?? $user->type ?? null;

        return $value ? strcasecmp($value, $role) === 0 : false;
    };

    $isManager = $hasRole('manager');
    $isAdmin = $hasRole('admin');
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-red-500 via-rose-500 to-amber-400 text-sm font-bold text-white">CB</span>
                <div class="leading-tight">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Cinema</p>
                    <p class="text-lg font-semibold text-white">Booking</p>
                </div>
            </a>
        </div>

        <div class="hidden items-center gap-6 md:flex">
            <a href="{{ url('/') }}" class="text-sm font-medium text-slate-200 hover:text-white">Home</a>
            <a href="{{ url('/movies') }}" class="text-sm font-medium text-slate-200 hover:text-white">Movies</a>

            @auth
                <a href="{{ url('/bookings') }}" class="text-sm font-medium text-slate-200 hover:text-white">My Bookings</a>
            @endauth

            @if ($isManager)
                <a href="{{ url('/manager/movies') }}" class="text-sm font-medium text-slate-200 hover:text-white">Manage Movies</a>
                <a href="{{ url('/manager/reports') }}" class="text-sm font-medium text-slate-200 hover:text-white">Reports</a>
            @endif

            @if ($isAdmin)
                <a href="{{ url('/admin/ticket-types') }}" class="text-sm font-medium text-slate-200 hover:text-white">Ticket Types</a>
                <a href="{{ url('/admin/logs') }}" class="text-sm font-medium text-slate-200 hover:text-white">Logs</a>
            @endif

            <div class="h-6 w-px bg-white/10"></div>

            @auth
                <a href="{{ route('profile.edit') }}" class="text-sm text-slate-300 hover:text-white">{{ $user->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:border-white/40">Log Out</button>
                </form>
            @else
                <a href="{{ route('auth.login') }}" class="rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:border-white/40">Login</a>
            @endauth
        </div>

        <button @click="open = ! open" class="inline-flex items-center justify-center rounded-full border border-white/20 p-2 text-white md:hidden">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': !open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{ 'block': open, 'hidden': !open }" class="hidden border-t border-white/10 bg-slate-950/95 px-4 pb-6 pt-4 md:hidden">
        <div class="flex flex-col gap-4">
            <a href="{{ url('/') }}" class="text-sm font-medium text-slate-200 hover:text-white">Home</a>
            <a href="{{ url('/movies') }}" class="text-sm font-medium text-slate-200 hover:text-white">Movies</a>

            @auth
                <a href="{{ url('/bookings') }}" class="text-sm font-medium text-slate-200 hover:text-white">My Bookings</a>
            @endauth

            @if ($isManager)
                <a href="{{ url('/manager/movies') }}" class="text-sm font-medium text-slate-200 hover:text-white">Manage Movies</a>
                <a href="{{ url('/manager/reports') }}" class="text-sm font-medium text-slate-200 hover:text-white">Reports</a>
            @endif

            @if ($isAdmin)
                <a href="{{ url('/admin/ticket-types') }}" class="text-sm font-medium text-slate-200 hover:text-white">Ticket Types</a>
                <a href="{{ url('/admin/logs') }}" class="text-sm font-medium text-slate-200 hover:text-white">Logs</a>
            @endif

            <div class="border-t border-white/10 pt-4">
                @auth
                    <div class="mb-3 text-xs uppercase tracking-[0.2em] text-slate-500">Signed in as {{ $user->name }}</div>
                    <div class="flex flex-col gap-3">
                        <a href="{{ route('profile.edit') }}" class="text-sm text-slate-200 hover:text-white">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:border-white/40">Log Out</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('auth.login') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:border-white/40">Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
