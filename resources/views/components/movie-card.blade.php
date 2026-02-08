@props([
    'title',
    'image' => null,
    'times' => [],
    'ctaLabel' => 'Book Now',
    'ctaHref' => '#',
    'tag' => null,
    'editHref' => null,
    'editAction' => null,
    'deleteHref' => null,
    'deleteAction' => null,
    'deleteLabel' => 'Delete Movie',
])

<article {{ $attributes->merge(['class' => 'group card-surface overflow-hidden transition hover:-translate-y-1 accent-glow']) }}>
    <div class="relative aspect-[2/3] overflow-hidden bg-gradient-to-br from-canvas-muted via-card-surface to-canvas">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }} cover" class="absolute inset-0 h-full w-full object-cover opacity-90 transition duration-500 group-hover:scale-105" />
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-canvas-muted via-card to-canvas"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
        @if ($tag)
            <span class="absolute left-4 top-4 rounded-full border border-white/20 bg-black/40 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-white">
                {{ $tag }}
            </span>
        @endif
    </div>

    <div class="p-4">
        <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
        @if (!empty($times))
            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-300">
                @foreach ($times as $time)
                    <span class="rounded-full border border-white/10 bg-canvas-muted px-3 py-1">{{ $time }}</span>
                @endforeach
            </div>
        @endif
        <div class="mt-4 flex items-center gap-3">
            <a href="{{ $ctaHref }}" class="btn-ghost w-full text-center">{{ $ctaLabel }}</a>

            @hasanyrole('manager|admin')
                @if ($editAction)
                    <button type="button" x-on:click="{{ $editAction }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-canvas-muted text-white transition hover:border-accent/60 hover:shadow-glow-soft" aria-label="Edit Movie">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M15.232 2.232a2.5 2.5 0 0 1 3.536 3.536l-9.9 9.9a2 2 0 0 1-.878.515l-3.42 1.026a.75.75 0 0 1-.93-.93l1.026-3.42a2 2 0 0 1 .515-.878l9.9-9.9Z" />
                            <path d="M12.5 4.5 15.5 7.5" />
                        </svg>
                    </button>
                @elseif ($editHref)
                    <a href="{{ $editHref }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-canvas-muted text-white transition hover:border-accent/60 hover:shadow-glow-soft" aria-label="Edit Movie">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M15.232 2.232a2.5 2.5 0 0 1 3.536 3.536l-9.9 9.9a2 2 0 0 1-.878.515l-3.42 1.026a.75.75 0 0 1-.93-.93l1.026-3.42a2 2 0 0 1 .515-.878l9.9-9.9Z" />
                            <path d="M12.5 4.5 15.5 7.5" />
                        </svg>
                    </a>
                @endif

                @if ($deleteAction)
                    <button type="button" x-on:click="{{ $deleteAction }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-rose-500/20 bg-rose-500/10 text-rose-200 transition hover:border-rose-500/60 hover:text-white" aria-label="Delete Movie">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.75 2.75a.75.75 0 0 0-1.5 0V3H4.5a.75.75 0 0 0 0 1.5h.75v10.25A2.5 2.5 0 0 0 7.75 17h4.5a2.5 2.5 0 0 0 2.5-2.5V4.5h.75a.75.75 0 0 0 0-1.5h-2.75v-.25a.75.75 0 0 0-1.5 0V3h-2.5v-.25Zm-.5 5a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .74.75v6a.75.75 0 0 1-1.5 0v-6Zm3-.75a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @elseif ($deleteHref)
                    <form method="POST" action="{{ $deleteHref }}" onsubmit="return confirm('{{ $deleteLabel }}?');">
                        @csrf
                        @method('delete')
                        <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-rose-500/20 bg-rose-500/10 text-rose-200 transition hover:border-rose-500/60 hover:text-white" aria-label="Delete Movie">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.75 2.75a.75.75 0 0 0-1.5 0V3H4.5a.75.75 0 0 0 0 1.5h.75v10.25A2.5 2.5 0 0 0 7.75 17h4.5a2.5 2.5 0 0 0 2.5-2.5V4.5h.75a.75.75 0 0 0 0-1.5h-2.75v-.25a.75.75 0 0 0-1.5 0V3h-2.5v-.25Zm-.5 5a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .74.75v6a.75.75 0 0 1-1.5 0v-6Zm3-.75a.75.75 0 0 0-.75.75v6a.75.75 0 0 0 1.5 0v-6a.75.75 0 0 0-.75-.75Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </form>
                @endif
            @endhasanyrole
        </div>
    </div>
</article>
