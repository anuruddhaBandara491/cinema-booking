<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Cinema Booking') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            @isset($header)
                <section class="border-b border-white/10 bg-slate-950/60">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </section>
            @endisset

            <main class="flex-1">
               {{ $slot }}
            </main>

            <footer class="border-t border-white/10 bg-slate-950">
                <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Experience cinema in a new light.</p>
                            <p class="text-xs text-slate-500">© {{ now()->year }} {{ config('app.name', 'Cinema Booking') }}. All rights reserved.</p>
                        </div>
                        <div class="flex gap-4 text-xs text-slate-400">
                            <a class="hover:text-white transition-colors" href="{{ url('/') }}">Home</a>
                            <a class="hover:text-white transition-colors" href="{{ url('/movies') }}">Movies</a>
                            <a class="hover:text-white transition-colors" href="{{ route('terms') }}">Terms & Conditions</a>
                            <a class="hover:text-white transition-colors" href="{{ url('/contact') }}">Contact</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
