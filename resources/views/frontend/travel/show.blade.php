@extends('layouts.frontend')

@php
    $metaDescription = $location->description
        ? \Illuminate\Support\Str::limit($location->description, 155)
        : 'Explore '.$location->name.', a '.$location->type->label().' on '.config('app.name').'. Discover traveller videos, reviews, and nearby places.';
@endphp

@section('title', e($location->name).' — '.config('app.name'))
@section('description', e($metaDescription))
@section('canonical', route('travel.show', $location->full_slug))
@if (! $location->indexable)
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 sm:py-14">
        @if (session('status') && ! in_array(session('status'), ['review-submitted', 'review-updated', 'review-deleted', 'report-submitted', 'report-duplicate'], true))
            <div class="mb-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                {{ session('status') }}
            </div>
        @endif

        @include('frontend.partials.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

        <header class="mt-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ $location->name }}</h1>

            <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-slate-400">
                <span class="inline-flex items-center rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 font-medium text-teal-300">
                    {{ $location->type->label() }}
                </span>
                @if ($location->city_name)
                    <span>{{ $location->city_name }}</span>
                @endif
                @if ($location->city_name && $location->country_code)
                    <span aria-hidden="true" class="text-slate-600">&middot;</span>
                @endif
                @if ($location->country_code)
                    <span>{{ $location->country_code }}</span>
                @endif
            </p>

            @if ($location->description)
                <p class="mt-5 max-w-3xl whitespace-pre-line leading-relaxed text-slate-300">{{ $location->description }}</p>
            @endif
        </header>

        @if (! is_null($location->latitude) && ! is_null($location->longitude))
            <div class="mt-8 overflow-hidden rounded-xl border border-white/10 bg-white/5"
                 role="img"
                 aria-label="Map placeholder for {{ $location->name }} at coordinates {{ number_format($location->latitude, 5) }}, {{ number_format($location->longitude, 5) }}">
                <div class="flex flex-col items-center justify-center gap-2 px-6 py-12 text-center">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6c0 3.53 4.36 8.5 5.55 9.82a.6.6 0 00.9 0C11.64 16.5 16 11.53 16 8a6 6 0 00-6-6zm0 8.25A2.25 2.25 0 1010 5.75a2.25 2.25 0 000 4.5z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <p class="text-sm font-medium text-slate-200">
                        {{ number_format($location->latitude, 5) }}, {{ number_format($location->longitude, 5) }}
                    </p>
                    <p class="max-w-md text-xs text-slate-500">
                        An interactive map is coming soon. These coordinates pinpoint {{ $location->name }}.
                    </p>
                </div>
            </div>
        @endif

        <section class="mt-12" aria-labelledby="places-heading">
            <h2 id="places-heading" class="text-lg font-semibold text-white">Places in {{ $location->name }}</h2>

            @if ($children->isNotEmpty())
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($children as $child)
                        @include('frontend.partials.location-card', ['location' => $child])
                    @endforeach
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                    <p class="mx-auto max-w-md text-sm text-slate-400">
                        Nothing has been added here yet. If you know {{ $location->name }}, help other travellers by adding the first place.
                    </p>
                    <a href="{{ route('register') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        Add a place
                    </a>
                </div>
            @endif
        </section>

        @if ($nearby->isNotEmpty())
            <section class="mt-12" aria-labelledby="nearby-heading">
                <h2 id="nearby-heading" class="text-lg font-semibold text-white">Nearby places</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($nearby as $place)
                        @include('frontend.partials.location-card', ['location' => $place, 'distance' => $place->distance_meters])
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-12" aria-labelledby="videos-heading">
            <h2 id="videos-heading" class="text-lg font-semibold text-white">Traveller videos</h2>

            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse (($videos ?? collect()) as $video)
                    @include('frontend.partials.video-card', ['video' => $video])
                @empty
                    <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center sm:col-span-2 lg:col-span-3">
                        <p class="mx-auto max-w-md text-sm text-slate-400">
                            No videos for {{ $location->name }} yet. If you have been here, be the first to share a video.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-12" aria-labelledby="events-heading">
            <h2 id="events-heading" class="text-lg font-semibold text-white">Upcoming events</h2>

            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse (($events ?? collect()) as $event)
                    @include('frontend.partials.event-card', ['event' => $event])
                @empty
                    <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center sm:col-span-2 lg:col-span-3">
                        <p class="mx-auto max-w-md text-sm text-slate-400">
                            No upcoming events in {{ $location->name }} yet.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section id="reviews" class="mt-12 scroll-mt-8" aria-labelledby="reviews-heading">
            <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-1">
                <h2 id="reviews-heading" class="text-lg font-semibold text-white">Reviews</h2>

                <p class="text-sm text-slate-400">
                    @if ($reviewSummary['count'] > 0)
                        <span class="font-medium text-teal-300" aria-hidden="true">&starf;</span>
                        <span class="font-medium text-slate-200">{{ $reviewSummary['average'] }}</span>
                        <span aria-hidden="true" class="text-slate-600">&middot;</span>
                        {{ $reviewSummary['count'] }} {{ \Illuminate\Support\Str::plural('review', $reviewSummary['count']) }}
                    @else
                        No reviews yet
                    @endif
                </p>
            </div>

            @if (in_array(session('status'), ['review-submitted', 'review-updated', 'review-deleted', 'report-submitted'], true))
                <div class="mt-4 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                    @switch(session('status'))
                        @case('review-submitted')
                            Thanks &mdash; your review has been submitted.
                            @break
                        @case('review-updated')
                            Your review has been updated.
                            @break
                        @case('review-deleted')
                            Your review has been deleted.
                            @break
                        @case('report-submitted')
                            Thanks &mdash; your report has been submitted.
                            @break
                    @endswitch
                </div>
            @elseif (session('status') === 'report-duplicate')
                <div class="mt-4 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-300" role="status">
                    You have already reported this review.
                </div>
            @endif

            <div class="mt-6">
                @auth
                    @if ($userReview === null)
                        @include('frontend.partials.review-form', ['location' => $location, 'review' => null])
                    @elseif ($userReview->status === \App\Enums\ReviewStatus::Approved)
                        <div class="rounded-xl border border-teal-500/20 bg-teal-500/5 p-5 sm:p-6">
                            <p class="text-sm font-medium text-teal-300">You reviewed this place</p>
                            <div class="mt-4">
                                @include('frontend.partials.review-card', ['review' => $userReview])
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-5 sm:p-6">
                            <p class="text-sm font-medium text-amber-300">Your review is awaiting moderation</p>
                            <div class="mt-4">
                                @include('frontend.partials.review-card', ['review' => $userReview])
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded-xl border border-white/10 bg-white/5 p-6">
                        <p class="text-sm text-slate-300">
                            <a href="{{ route('login') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">Log in</a>
                            or
                            <a href="{{ route('register') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">sign up</a>
                            to share your experience of {{ $location->name }}.
                        </p>
                    </div>
                @endauth
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($reviews as $review)
                    @continue($userReview !== null && $review->is($userReview))
                    @include('frontend.partials.review-card', ['review' => $review])
                @empty
                    <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                        <p class="mx-auto max-w-md text-sm text-slate-400">
                            Be the first to review {{ $location->name }}.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-12" aria-labelledby="share-heading">
            <div class="rounded-xl border border-teal-500/20 bg-teal-500/5 p-6 sm:p-8">
                <h2 id="share-heading" class="text-lg font-semibold text-white">Be the first to share {{ $location->name }}</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Traveller videos and reviews for this place will appear here. If you have been to {{ $location->name }}, create an account to share your experience and help the next traveller.
                </p>
                <a href="{{ route('register') }}"
                   class="mt-5 inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Create an account
                </a>
            </div>
        </section>
    </section>
@endsection
