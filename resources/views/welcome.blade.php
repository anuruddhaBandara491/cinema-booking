<x-app-layout>
    @php
        $sliderImages = \App\Models\SliderImage::orderBy('order')->get();
        $sliderSlides = $sliderImages->map(function($img) {
            $movie = \App\Models\Movie::where('title', $img->title)->first();
            return [
                'image' => asset('storage/' . $img->image_path),
                'title' => $img->title,
                'subtitle' => $img->subtitle,
                'buy_ticket_url' => ($movie && $movie->book_now) ? route('bookings.flow', $movie) : null,
                'trailer_url' => $img->trailer_url ?? '#',
            ];
        })->values();
    @endphp
    <section
        x-data="sliderComponent({{ $sliderSlides->toJson() }})"
        x-init="start()"
        @mouseenter="stop()" @mouseleave="start()"
        class="relative w-full h-[500px] md:h-[800px] overflow-hidden mb-10 rounded-2xl shadow-lg"
    >
            <script>
            function sliderComponent(slides) {
                return {
                    slides: slides,
                    active: 0,
                    interval: null,
                    start() {
                        if (this.slides.length > 1) {
                            this.interval = setInterval(() => this.next(), 4000)
                        }
                    },
                    stop() { if (this.interval) clearInterval(this.interval) },
                    next() { if (this.slides.length) this.active = (this.active + 1) % this.slides.length },
                    prev() { if (this.slides.length) this.active = (this.active - 1 + this.slides.length) % this.slides.length }
                }
            }
            </script>
        <template x-if="slides.length === 0">
            <div class=\"flex items-center justify-center h-full text-white text-xl\">No slider images available.</div>
        </template>
        <!-- Slides -->
        <template x-for="(slide, i) in slides" :key="i">
            <div
                x-show="active === i"
                x-transition:enter="transition-opacity duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity duration-700"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 w-full h-full"
            >
                <img :src="slide.image" alt="" class="w-full h-full object-contain object-center" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                <div class="absolute left-10 bottom-10 text-white space-y-2">
                    <h2 class="text-3xl md:text-5xl font-bold" x-text="slide.title"></h2>
                    <p class="text-lg md:text-2xl" x-text="slide.subtitle"></p>
                    <div class="mt-6 flex gap-4">
                        <a :href="slide.trailer_url" target="_blank" class="px-6 py-3 rounded bg-black/60 hover:bg-black/80 text-white font-semibold text-lg">Watch Trailer</a>

                    </div>
                </div>
            </div>
        </template>

        <!-- Controls -->
        <button @click="prev" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white rounded-full p-2" x-show="slides.length > 1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white rounded-full p-2" x-show="slides.length > 1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        <!-- Dots -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2" x-show="slides.length > 1">
            <template x-for="(slide, i) in slides" :key="i">
                <button
                    @click="active = i"
                    :class="{'bg-white': active === i, 'bg-white/50': active !== i}"
                    class="w-3 h-3 rounded-full transition-all"
                ></button>
            </template>
        </div>
    </section>


    <section class="bg-slate-950 py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div x-data="{ tab: 'now' }">
                <div class="flex items-center gap-8 mb-8">
                    <button @click="tab = 'now'" :class="tab === 'now' ? 'text-white font-bold' : 'text-slate-400 font-semibold'" class="text-xl uppercase tracking-wider focus:outline-none relative">
                        NOW SHOWING
                        <span x-show="tab === 'now'" class="block mt-2 h-1 w-32 bg-rose-500 absolute left-0 bottom-[-8px]"></span>
                    </button>
                    <button @click="tab = 'soon'" :class="tab === 'soon' ? 'text-white font-bold' : 'text-slate-400 font-semibold'" class="text-xl uppercase tracking-wider focus:outline-none relative">
                        COMING SOON
                        <span x-show="tab === 'soon'" class="block mt-2 h-1 w-32 bg-rose-500 absolute left-0 bottom-[-8px]"></span>
                    </button>
                </div>
                <div x-show="tab === 'now'">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Now Showing</p>
                            <h2 class="text-3xl font-semibold text-white">Top picks this week</h2>
                        </div>
                        <div class="flex flex-col gap-3 text-right sm:items-end">
                            <a href="{{ url('/movies') }}" class="text-sm text-rose-300 hover:text-rose-200">View full lineup →</a>
                        </div>
                    </div>
                    @if ($nowShowing->isEmpty())
                        <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                            No movies are showing right now. Check back soon.
                        </div>
                    @else
                        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($nowShowing as $movie)
                                <x-movie-card
                                    :title="$movie->title"
                                    :start-date="optional($movie->show_start_date)->format('M d, Y')"
                                    :end-date="optional($movie->show_end_date)->format('M d, Y')"
                                    :times="$movie->show_times ?? []"
                                    :image="$movie->cover_image_url"
                                    :book-now="$movie->book_now"
                                    tag="Now Showing"
                                    cta-label="Book Now"
                                    cta-href="{{ route('bookings.flow', $movie) }}"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
                <div x-show="tab === 'soon'">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Upcoming</p>
                            <h2 class="text-3xl font-semibold text-white">Coming soon</h2>
                        </div>
                        <div class="flex flex-col gap-3 text-right sm:items-end">
                            <a href="{{ url('/movies') }}" class="text-sm text-rose-300 hover:text-rose-200">View full lineup →</a>
                        </div>
                    </div>
                    @if ($upcoming->isEmpty())
                        <div class="mt-8 rounded-2xl border border-white/10 bg-canvas-muted p-10 text-center text-sm text-slate-400">
                            No upcoming movies yet. Check back soon.
                        </div>
                    @else
                        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($upcoming as $movie)
                                <x-movie-card
                                    :title="$movie->title"
                                    :start-date="optional($movie->show_start_date)->format('M d, Y')"
                                    :end-date="optional($movie->show_end_date)->format('M d, Y')"
                                    :times="$movie->show_times ?? []"
                                    :image="$movie->cover_image_url"
                                    :book-now="$movie->book_now"
                                    tag="Upcoming"
                                    cta-label="View Details"
                                    cta-href="{{ route('movies.index') }}"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
