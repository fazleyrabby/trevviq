@extends('layouts.frontend')

@section('title', 'Saved Items — '.config('app.name'))
@section('canonical', route('saved.index'))

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 sm:py-14">
        <header>
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Saved Items</h1>
            <p class="mt-3 text-sm text-slate-400">Places, videos, and events you want to remember.</p>
        </header>

        <div class="mt-12 space-y-16">
            <section aria-labelledby="saved-locations-heading">
                <h2 id="saved-locations-heading" class="text-xl font-bold text-white">Locations</h2>
                
                @if ($savedLocations->isNotEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($savedLocations as $saved)
                            @include('frontend.partials.location-card', ['location' => $saved->location])
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">You haven't saved any locations yet.</p>
                @endif
            </section>

            <section aria-labelledby="saved-videos-heading">
                <h2 id="saved-videos-heading" class="text-xl font-bold text-white">Videos</h2>
                
                @if ($savedVideos->isNotEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($savedVideos as $saved)
                            <a href="{{ route('videos.show', $saved->video) }}" class="group relative block overflow-hidden rounded-xl bg-slate-900 aspect-[9/16]">
                                @if ($saved->video->thumbnailUrl())
                                    <img src="{{ $saved->video->thumbnailUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105 group-hover:opacity-75"/>
                                @else
                                    <div class="absolute inset-0 bg-slate-800"></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                                <div class="absolute bottom-0 p-4">
                                    <p class="text-sm font-medium text-white">{{ $saved->video->title ?? 'Video' }}</p>
                                    @if($saved->video->location)
                                        <p class="text-xs text-slate-300">{{ $saved->video->location->name }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">You haven't saved any videos yet.</p>
                @endif
            </section>

            <section aria-labelledby="saved-events-heading">
                <h2 id="saved-events-heading" class="text-xl font-bold text-white">Events</h2>
                
                @if ($savedEvents->isNotEmpty())
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($savedEvents as $saved)
                            <a href="{{ route('events.show', $saved->event) }}" class="block rounded-xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                                <h3 class="font-medium text-white">{{ $saved->event->name }}</h3>
                                <p class="mt-1 text-xs text-slate-400">{{ $saved->event->starts_at->format('M j, Y') }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">You haven't saved any events yet.</p>
                @endif
            </section>
        </div>
    </section>
@endsection
