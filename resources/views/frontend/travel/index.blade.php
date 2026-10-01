@extends('layouts.frontend')

@section('title', 'Explore destinations — '.config('app.name'))
@section('description', 'Browse every country on '.config('app.name').' and discover the regions, cities, and places travellers have actually been.')
@section('canonical', route('travel.index'))

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-12 sm:py-16">
        <header class="max-w-2xl">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Explore the world</h1>
            <p class="mt-3 text-slate-400">
                Pick a country to browse its regions, cities, and places — all connected to stories and videos from real travellers.
            </p>
        </header>

        @if ($countries->isEmpty())
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-10 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-1.5a6.5 6.5 0 100-13 6.5 6.5 0 000 13z" clip-rule="evenodd"/>
                        <path d="M10 5.5a.75.75 0 01.75.75v.6l1.6-.64a.75.75 0 11.56 1.39l-1.6.64.98 1.42a.75.75 0 11-1.24.85L10 9.09l-1.05 1.52a.75.75 0 01-1.24-.85l.98-1.42-1.6-.64a.75.75 0 11.56-1.39l1.6.64v-.6A.75.75 0 0110 5.5z"/>
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-white">Destinations are being imported</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                    We are still building the world map. Check back soon to explore countries, cities, and places.
                </p>
                <a href="{{ route('home') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Back to home
                </a>
            </div>
        @else
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($countries as $country)
                    @include('frontend.partials.location-card', ['location' => $country])
                @endforeach
            </div>
        @endif
    </section>
@endsection
