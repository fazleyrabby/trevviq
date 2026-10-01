@php
    $thumbnail = $video->thumbnailUrl();
    $title = $video->title ?: 'Untitled story';
@endphp

<article class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 transition hover:border-teal-500/40 hover:bg-white/10 focus-within:ring-2 focus-within:ring-teal-500">
    <a href="{{ route('videos.show', $video) }}"
       class="absolute inset-0 z-10"
       aria-label="Watch {{ $title }}"></a>

    <div class="relative aspect-[9/16] w-full overflow-hidden bg-slate-900">
        @if ($thumbnail)
            <img src="{{ $thumbnail }}"
                 alt=""
                 loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-slate-600">
                <svg class="h-10 w-10" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M4 5a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2H4zm7 2.5l4 3.5-4 3.5V7.5z"/>
                </svg>
            </div>
        @endif

        @if (! is_null($video->duration))
            <span class="absolute bottom-2 right-2 rounded bg-slate-950/80 px-1.5 py-0.5 text-xs font-medium text-slate-100">
                {{ $video->duration }}s
            </span>
        @endif
    </div>

    <div class="p-4">
        <h3 class="truncate font-semibold text-white group-hover:text-teal-200">{{ $title }}</h3>

        <p class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-slate-400">
            @if ($video->location)
                <a href="{{ route('travel.show', $video->location->full_slug) }}"
                   class="relative z-20 underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    {{ $video->location->name }}
                </a>
            @endif

            @if ($video->location && $video->user)
                <span aria-hidden="true" class="text-slate-600">&middot;</span>
            @endif

            @if ($video->user)
                <a href="{{ route('traveller.show', $video->user->username) }}"
                   class="relative z-20 underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    {{ $video->user->name }}
                </a>
            @endif
        </p>

        <p class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 17l-1.3-1.18C4.4 12.06 1.5 9.42 1.5 6.2 1.5 3.56 3.56 1.5 6.2 1.5c1.49 0 2.92.69 3.8 1.78A5.06 5.06 0 0110 4.2a5.06 5.06 0 010-0.92A5.06 5.06 0 0113.8 1.5c2.64 0 4.7 2.06 4.7 4.7 0 3.22-2.9 5.86-7.2 9.62L10 17z"/>
            </svg>
            <span>{{ number_format($video->like_count) }} {{ \Illuminate\Support\Str::plural('like', $video->like_count) }}</span>
        </p>
    </div>
</article>
