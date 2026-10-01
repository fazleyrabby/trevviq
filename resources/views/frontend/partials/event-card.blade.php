@php
    $excerpt = $event->description ? \Illuminate\Support\Str::limit($event->description, 120) : null;
@endphp

<article class="group relative flex flex-col overflow-hidden rounded-xl border border-white/10 bg-white/5 transition hover:border-teal-500/40 hover:bg-white/10 focus-within:ring-2 focus-within:ring-teal-500">
    <a href="{{ route('events.show', $event) }}"
       class="absolute inset-0 z-10"
       aria-label="View event {{ $event->name }}"></a>

    <div class="flex items-center gap-3 border-b border-white/5 p-4">
        <span class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg bg-teal-500/15 text-teal-300">
            <span class="text-[10px] font-semibold uppercase leading-none">{{ $event->starts_at->format('M') }}</span>
            <span class="text-base font-bold leading-tight">{{ $event->starts_at->format('j') }}</span>
        </span>
        <div class="min-w-0">
            <h3 class="truncate font-semibold text-white group-hover:text-teal-200">{{ $event->name }}</h3>
            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $event->starts_at->format('D, j M Y · g:ia') }}</p>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-4">
        @if ($event->location)
            <p class="flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 3.53 4.36 8.5 5.55 9.82a.6.6 0 00.9 0C11.64 16.5 16 11.53 16 8a6 6 0 00-6-6zm0 8.25A2.25 2.25 0 1010 5.75a2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                </svg>
                <a href="{{ route('travel.show', $event->location->full_slug) }}"
                   class="relative z-20 truncate underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    {{ $event->location->name }}
                </a>
            </p>
        @endif

        @if ($excerpt)
            <p class="mt-3 line-clamp-3 text-sm text-slate-300">{{ $excerpt }}</p>
        @endif
    </div>
</article>
