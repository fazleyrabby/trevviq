@extends('layouts.frontend')

@php
    $viewer = auth()->user();
    $isOwner = $event->isOwnedBy($viewer);
    $metaDescription = $event->description
        ? \Illuminate\Support\Str::limit($event->description, 155)
        : 'Event on '.config('app.name').' starting '.$event->starts_at->format('D, j M Y · g:ia').'.';
    $buttonBase = 'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500';
@endphp

@section('title', e($event->name).' — '.config('app.name'))
@section('description', e($metaDescription))
@section('canonical', route('events.show', $event))
@if (! $event->status->isPublic())
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
        @if (! $event->status->isPublic())
            <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300" role="status">
                This event is <span class="font-semibold">{{ $event->status->label() }}</span> and only visible to its owner.
            </div>
        @endif

        <header>
            <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">{{ $event->name }}</h1>

            <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-400">
                @if ($event->location)
                    <a href="{{ route('travel.show', $event->location->full_slug) }}"
                       class="font-medium text-slate-200 underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        {{ $event->location->name }}
                    </a>
                @endif

                @if ($event->location && $event->organizer)
                    <span aria-hidden="true" class="text-slate-600">&middot;</span>
                @endif

                @if ($event->organizer)
                    <span>Organised by</span>
                    <a href="{{ route('traveller.show', $event->organizer->username) }}"
                       class="font-medium text-slate-200 underline-offset-2 transition hover:text-teal-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        {{ $event->organizer->name }}
                    </a>
                @endif
            </p>
        </header>

        <div class="mt-6 rounded-xl border border-white/10 bg-white/5 p-5 sm:p-6">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date and time</dt>
                    <dd class="mt-1 text-slate-200">
                        <time datetime="{{ $event->starts_at->toIso8601String() }}">{{ $event->starts_at->format('D, j M Y · g:ia') }}</time>
                        @if ($event->ends_at)
                            <span class="text-slate-500">
                                &ndash;
                                <time datetime="{{ $event->ends_at->toIso8601String() }}">{{ $event->ends_at->format('D, j M Y · g:ia') }}</time>
                            </span>
                        @endif
                    </dd>
                </div>

                @if ($event->address)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Address</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-300">{{ $event->address }}</dd>
                    </div>
                @endif

                @if ($event->website_url)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Website</dt>
                        <dd class="mt-1">
                            <a href="{{ $event->website_url }}"
                               target="_blank"
                               rel="noopener"
                               class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                {{ $event->website_url }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($event->description)
            <p class="mt-6 max-w-2xl whitespace-pre-line leading-relaxed text-slate-300">{{ $event->description }}</p>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-3">
            @auth
                <form method="POST" action="{{ route('events.save', $event) }}">
                    @csrf
                    <button type="submit"
                            aria-pressed="{{ $event->isSavedBy(auth()->user()) ? 'true' : 'false' }}"
                            class="{{ $buttonBase }} {{ $event->isSavedBy(auth()->user()) ? 'border-teal-500/40 bg-teal-500/15 text-teal-200' : 'border-white/10 text-slate-300 hover:bg-white/5' }}">
                        {{ $event->isSavedBy(auth()->user()) ? 'Saved' : 'Save' }}
                    </button>
                </form>
                @if ($isOwner)
                    <form method="POST"
                          action="{{ route('events.destroy', $event) }}"
                          onsubmit="return confirm('Delete this event? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="{{ $buttonBase }} border-white/10 text-rose-300 hover:bg-rose-500/10">
                            Delete event
                        </button>
                    </form>
                @else
                    <details class="relative">
                        <summary class="{{ $buttonBase }} cursor-pointer list-none border-white/10 text-slate-400 hover:bg-white/5">
                            Report
                        </summary>

                        <form method="POST"
                              action="{{ route('events.report', $event) }}"
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
                        to report this event.
                    </p>
                </div>
            @endauth
        </div>
    </section>
@endsection
