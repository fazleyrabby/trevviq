@extends('layouts.frontend')

@php
    $viewer = auth()->user();
    $thumbnail = $video->thumbnailUrl();
    $media = $video->mediaUrl();
    $isOwner = $video->isOwnedBy($viewer);
    $isVisible = $video->status === \App\Enums\VideoStatus::Published
        && $video->visibility === \App\Enums\VideoVisibility::Public;
    $buttonBase = 'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500';
@endphp

@section('title', ($video->title ?: 'Traveller story').' — '.config('app.name'))
@section('canonical', route('videos.show', $video))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
        @if (! $isVisible)
            <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300" role="status">
                This video is <span class="font-semibold">{{ $video->status->label() }}</span> and only visible to its owner.
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-white/10 bg-black">
            @if ($media)
                <video controls
                       preload="metadata"
                       poster="{{ $thumbnail }}"
                       playsinline
                       class="aspect-video h-auto w-full bg-black">
                    <source src="{{ $media }}" type="{{ $video->mime_type }}">
                    Your browser cannot play this video.
                    <a href="{{ $media }}" class="text-teal-300 underline underline-offset-2">Download the video</a> instead.
                </video>
            @else
                <div class="flex flex-col items-center justify-center gap-2 px-6 py-16 text-center">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 text-slate-400">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11.5H9v-5h2v5zm0-7H9V5h2v1.5z"/>
                        </svg>
                    </span>
                    <p class="text-sm font-medium text-slate-200">This video is still processing.</p>
                    <p class="max-w-sm text-xs text-slate-500">Check back soon &mdash; processing usually takes a few minutes.</p>
                </div>
            @endif
        </div>

        <p class="mt-2 text-xs text-slate-500">
            Captions are not yet available for this video. Playback never starts automatically with sound.
        </p>

        <header class="mt-6">
            <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">{{ $video->title ?: 'Untitled story' }}</h1>

            <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-400">
                @if ($video->user)
                    <a href="{{ route('traveller.show', $video->user->username) }}"
                       class="font-medium text-slate-200 underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        {{ $video->user->name }}
                    </a>
                @endif

                @if ($video->user && $video->location)
                    <span aria-hidden="true" class="text-slate-600">&middot;</span>
                @endif

                @if ($video->location)
                    <a href="{{ route('travel.show', $video->location->full_slug) }}"
                       class="underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        {{ $video->location->name }}
                    </a>
                @endif
            </p>

            <p class="mt-2 flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                <span>{{ number_format($video->view_count) }} {{ \Illuminate\Support\Str::plural('view', $video->view_count) }}</span>
                <span aria-hidden="true" class="text-slate-600">&middot;</span>
                <span>{{ number_format($video->like_count) }} {{ \Illuminate\Support\Str::plural('like', $video->like_count) }}</span>
                @if ($video->duration)
                    <span aria-hidden="true" class="text-slate-600">&middot;</span>
                    <span>{{ $video->duration }}s</span>
                @endif
            </p>

            @if ($video->description)
                <p class="mt-5 max-w-2xl leading-relaxed text-slate-300">{{ $video->description }}</p>
            @endif
        </header>

        <div class="mt-8 flex flex-wrap items-center gap-3">
            @auth
                <form method="POST" action="{{ route('videos.like', $video) }}">
                    @csrf
                    <button type="submit"
                            aria-pressed="{{ $video->isLikedBy($viewer) ? 'true' : 'false' }}"
                            class="{{ $buttonBase }} {{ $video->isLikedBy($viewer) ? 'border-teal-500/40 bg-teal-500/15 text-teal-200' : 'border-white/10 text-slate-300 hover:bg-white/5' }}">
                        Like ({{ $video->like_count }})
                    </button>
                </form>

                <form method="POST" action="{{ route('videos.save', $video) }}">
                    @csrf
                    <button type="submit"
                            aria-pressed="{{ $video->isSavedBy($viewer) ? 'true' : 'false' }}"
                            class="{{ $buttonBase }} {{ $video->isSavedBy($viewer) ? 'border-teal-500/40 bg-teal-500/15 text-teal-200' : 'border-white/10 text-slate-300 hover:bg-white/5' }}">
                        Save
                    </button>
                </form>

                <form method="POST" action="{{ route('videos.share', $video) }}">
                    @csrf
                    <button type="submit" class="{{ $buttonBase }} border-white/10 text-slate-300 hover:bg-white/5">
                        Share ({{ $video->share_count }})
                    </button>
                </form>

                @if ($isOwner)
                    <form method="POST"
                          action="{{ route('videos.destroy', $video) }}"
                          onsubmit="return confirm('Delete this video? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="{{ $buttonBase }} border-white/10 text-rose-300 hover:bg-rose-500/10">
                            Delete
                        </button>
                    </form>
                @else
                    <details class="relative">
                        <summary class="{{ $buttonBase }} cursor-pointer list-none border-white/10 text-slate-400 hover:bg-white/5">
                            Report
                        </summary>

                        <form method="POST"
                              action="{{ route('videos.report', $video) }}"
                              class="absolute left-0 z-20 mt-2 w-64 space-y-3 rounded-xl border border-white/10 bg-slate-900 p-4 shadow-xl">
                            @csrf

                            <div>
                                <label for="report-reason" class="block text-xs font-medium text-slate-300">Reason</label>
                                <select id="report-reason"
                                        name="reason"
                                        required
                                        class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950/60 px-2.5 py-1.5 text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    @foreach (\App\Enums\ReportReason::cases() as $reason)
                                        <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>{{ $reason->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="report-description" class="block text-xs font-medium text-slate-300">
                                    Details <span class="font-normal text-slate-500">(optional)</span>
                                </label>
                                <textarea id="report-description"
                                          name="description"
                                          rows="2"
                                          maxlength="1000"
                                          class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950/60 px-2.5 py-1.5 text-xs text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
                            </div>

                            <button type="submit"
                                    class="w-full rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                                Report
                            </button>
                        </form>
                    </details>
                @endif
            @else
                <div class="rounded-xl border border-white/10 bg-white/5 p-5">
                    <p class="text-sm text-slate-300">
                        <a href="{{ route('login') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">Log in</a>
                        or
                        <a href="{{ route('register') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">sign up</a>
                        to like, save, and share this video.
                    </p>
                </div>
            @endauth
        </div>
    </section>
@endsection
