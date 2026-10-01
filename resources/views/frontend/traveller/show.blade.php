@extends('layouts.frontend')

@php
    $metaDescription = $user->bio
        ? \Illuminate\Support\Str::limit($user->bio, 155)
        : 'Traveller profile of '.$user->name.' (@'.$user->username.'). See places visited, reviews, and travel history on '.config('app.name').'.';
@endphp

@section('title', $user->name.' — '.config('app.name'))
@section('description', e($metaDescription))
@section('canonical', route('traveller.show', $user->username))

@section('content')
    <section class="mx-auto max-w-4xl px-6 py-10 sm:py-14">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-center gap-4">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="h-20 w-20 rounded-full object-cover"/>
                    @else
                        <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-teal-500 text-2xl font-bold text-white" aria-hidden="true">
                            {{ $user->initials() }}
                        </span>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                        <p class="mt-0.5 text-sm text-slate-400">&#64;{{ $user->username }}</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium text-teal-300">
                                {{ $user->role->label() }}
                            </span>
                            @if ($user->country_code)
                                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium text-slate-300">
                                    {{ $user->country_code }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @auth
                    @if ($isSelf)
                        <a href="{{ route('profile.edit') }}"
                           class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                            Edit profile
                        </a>
                    @elseif ($isFollowing)
                        <form method="POST" action="{{ route('follows.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg border border-teal-500/40 bg-teal-500/15 px-4 py-2 text-sm font-medium text-teal-200 transition hover:bg-teal-500/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                Following
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('follows.store', $user) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                                Follow
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Follow
                    </a>
                @endauth
            </div>

            <div class="mt-6">
                @if ($user->bio)
                    <p class="whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $user->bio }}</p>
                @else
                    <p class="text-sm text-slate-500">No bio yet.</p>
                @endif
            </div>

            <dl class="mt-8 grid grid-cols-2 gap-4 border-t border-white/10 pt-6 sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Places</dt>
                    <dd class="mt-1 text-lg font-semibold text-white">{{ $stats['places'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Reviews</dt>
                    <dd class="mt-1 text-lg font-semibold text-white">{{ $stats['reviews'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Followers</dt>
                    <dd class="mt-1 text-lg font-semibold text-white">{{ $stats['followers'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Following</dt>
                    <dd class="mt-1 text-lg font-semibold text-white">{{ $stats['following'] }}</dd>
                </div>
            </dl>
        </div>

        <section class="mt-12" aria-labelledby="reviews-heading">
            <h2 id="reviews-heading" class="text-lg font-semibold text-white">Reviewed places</h2>

            <div class="mt-4 space-y-4">
                @forelse ($reviews as $review)
                    @include('frontend.partials.review-card', ['review' => $review])
                @empty
                    <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                        <p class="mx-auto max-w-md text-sm text-slate-400">
                            {{ $isSelf ? "You haven't written any reviews yet." : $user->name.' has not written any reviews yet.' }}
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-12" aria-labelledby="places-heading">
            <h2 id="places-heading" class="text-lg font-semibold text-white">Places visited</h2>

            <div class="mt-4 space-y-3">
                @forelse ($visits as $visit)
                    <div>
                        @include('frontend.partials.location-card', ['location' => $visit->location])
                        @if ($visit->visited_at)
                            <p class="mt-1.5 pl-1 text-xs text-slate-500">Visited {{ $visit->visited_at->format('M Y') }}</p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                        <p class="mx-auto max-w-md text-sm text-slate-400">
                            {{ $isSelf ? "You haven't marked any places yet." : $user->name.' has not marked any places yet.' }}
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
