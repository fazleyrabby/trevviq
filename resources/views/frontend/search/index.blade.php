@extends('layouts.frontend')

@section('title', $term !== '' ? 'Search: '.e($term).' — '.config('app.name') : 'Search — '.config('app.name'))
@section('description', $term !== ''
    ? 'Places matching '.e($term).' on '.config('app.name').'.'
    : 'Search destinations worldwide on '.config('app.name').'.')
@section('canonical', route('search.index'))

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-12 sm:py-16">
        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Search</h1>

        <form method="GET"
              action="{{ route('search.index') }}"
              role="search"
              class="mt-6 flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 p-2">
            <label for="search-q" class="sr-only">Search destinations</label>
            <input id="search-q"
                   type="search"
                   name="q"
                   value="{{ $term }}"
                   placeholder="Search destinations, places, events..."
                   class="w-full bg-transparent px-3 py-2 text-slate-200 placeholder:text-slate-500 focus:outline-none"/>
            <button type="submit"
                    class="shrink-0 rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                Search
            </button>
        </form>

        @if ($term === '')
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-8 text-center sm:p-10">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a.75.75 0 11-1.06 1.06l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-white">Search destinations worldwide</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                    Find countries, cities, and unforgettable places. Try one of these to start exploring.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    @foreach (['Paris', 'Tokyo', 'Patenga Beach', 'Chattogram', 'London'] as $example)
                        <a href="{{ route('search.index', ['q' => $example]) }}"
                           class="rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-sm text-slate-200 transition hover:border-teal-500/40 hover:bg-white/10 hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                            {{ $example }}
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif ($results->isEmpty())
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-8 text-center sm:p-10">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a.75.75 0 11-1.06 1.06l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-white">No places found for &ldquo;{{ $term }}&rdquo;</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                    Try a different spelling, a nearby city, or a broader search term.
                </p>
                <a href="{{ route('travel.index') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Browse destinations by country
                </a>
            </div>
        @else
            <p class="mt-8 text-sm text-slate-400">
                {{ number_format($results->total()) }} {{ \Illuminate\Support\Str::plural('result', $results->total()) }} for &ldquo;{{ $term }}&rdquo;
            </p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($results as $location)
                    @include('frontend.partials.location-card', ['location' => $location, 'distance' => $location->distance_meters])
                @endforeach
            </div>

            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif
    </section>
@endsection
