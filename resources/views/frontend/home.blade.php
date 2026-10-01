@extends('layouts.frontend')

@section('title', config('app.name').' — Discover the world through travellers')
@section('description', 'Discover the world through people who have actually been there. Search destinations, browse popular cities and countries, and find the places still to be discovered.')
@section('canonical', route('home'))

@section('content')
    <section class="mx-auto max-w-4xl px-6 py-20 text-center sm:py-28">
        <p class="mb-4 inline-block rounded-full border border-teal-500/30 bg-teal-500/10 px-4 py-1 text-xs font-medium tracking-wide text-teal-300">
            Discover the world through people who have actually been there
        </p>
        <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-6xl">
            Discover somewhere new.
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-400">
            Every place, story, and video connected to the real world. Search a destination and see what travellers found.
        </p>

        <form method="GET"
              action="{{ route('search.index') }}"
              role="search"
              class="mx-auto mt-10 flex max-w-xl items-center gap-2 rounded-xl border border-white/10 bg-white/5 p-2">
            <label for="home-search" class="sr-only">Search destinations</label>
            <input id="home-search"
                   type="search"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Search destinations, places, events..."
                   class="w-full bg-transparent px-3 py-2 text-slate-200 placeholder:text-slate-500 focus:outline-none"/>
            <button type="submit"
                    class="shrink-0 rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                Search
            </button>
        </form>
        <p class="mt-3 text-xs text-slate-500">
            <a href="{{ route('travel.index') }}" class="text-teal-300 hover:text-teal-200">Browse destinations by country &rarr;</a>
        </p>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-12" aria-labelledby="trending-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="trending-heading" class="text-2xl font-bold tracking-tight text-white">Trending destinations</h2>
        </div>

        @if ($trendingDestinations->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                Nothing is trending yet. Popular places will appear here as travellers start exploring.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($trendingDestinations as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-12" aria-labelledby="cities-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="cities-heading" class="text-2xl font-bold tracking-tight text-white">Popular cities</h2>
            <a href="{{ route('explore.index', ['type' => 'city']) }}"
               class="text-sm font-medium text-teal-300 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                See all &rarr;
            </a>
        </div>

        @if ($popularCities->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                No cities to show yet. Cities will appear here once destinations are imported.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularCities as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-12" aria-labelledby="countries-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="countries-heading" class="text-2xl font-bold tracking-tight text-white">Explore by country</h2>
            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ route('travel.index') }}"
                   class="text-teal-300 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    All countries &rarr;
                </a>
                <a href="{{ route('explore.index') }}"
                   class="text-teal-300 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Explore &rarr;
                </a>
            </div>
        </div>

        @if ($popularCountries->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                Countries are being imported. Check back soon to explore the world.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularCountries as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-12" aria-labelledby="gems-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="gems-heading" class="text-2xl font-bold tracking-tight text-white">Hidden gems</h2>
                <p class="mt-1 text-sm text-slate-400">Places still to be discovered.</p>
            </div>
        </div>

        @if ($hiddenGems->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                Every place has a traveller story so far. New gems will surface here as they are added.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($hiddenGems as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-16" aria-labelledby="recent-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 id="recent-heading" class="text-2xl font-bold tracking-tight text-white">Recently added</h2>
        </div>

        @if ($recentlyAdded->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                Nothing here yet. The newest destinations will show up in this space.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($recentlyAdded as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-16" aria-labelledby="events-heading">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="events-heading" class="text-2xl font-bold tracking-tight text-white">Upcoming events</h2>
                <p class="mt-1 text-sm text-slate-400">Gatherings tied to the places travellers love.</p>
            </div>
            <a href="{{ route('events.index') }}"
               class="text-sm font-medium text-teal-300 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                See all &rarr;
            </a>
        </div>

        @if (($upcomingEvents ?? collect())->isEmpty())
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 p-6 text-sm text-slate-400">
                No upcoming events yet. Check back soon, or create the first one for a place you know.
            </p>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($upcomingEvents as $event)
                    @include('frontend.partials.event-card', ['event' => $event])
                @endforeach
            </div>
        @endif
    </section>
@endsection
