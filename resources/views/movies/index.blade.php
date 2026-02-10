<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Movies</p>
            <h2 class="text-2xl font-semibold text-white">Now showing</h2>
        </div>
    </x-slot>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($movies->isEmpty())
            <div class="rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                No movies are available yet. Check back soon.
            </div>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($movies as $movie)
                    <x-movie-card
                        :title="$movie->title"
                        :times="$movie->show_times ?? []"
                        :tag="$movie->is_published ? 'Now Showing' : ($movie->is_upcoming ? 'Upcoming' : 'Draft')"
                        :image="$movie->cover_image_url"
                        cta-label="Book Now"
                        cta-href="@auth{{ route('bookings.flow') }}@else{{ route('auth.login') }}@endauth"
                    />
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
