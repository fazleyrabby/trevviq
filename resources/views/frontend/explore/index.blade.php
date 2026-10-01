@extends('layouts.frontend')

@section('title', 'Explore destinations — '.config('app.name'))
@section('description', 'Browse cities, countries, regions, and places from around the world on '.config('app.name').'.')
@section('canonical', route('explore.index'))

@section('content')
    <section class="mx-auto max-w-6xl px-6 py-12 sm:py-16">
        <header class="max-w-2xl">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Explore</h1>
            <p class="mt-3 text-slate-400">
                Browse destinations by type and discover the places travellers have actually been.
            </p>
        </header>

        <nav aria-label="Filter destinations" class="mt-8 flex flex-wrap gap-2">
            @foreach ($filters as $key => $label)
                <a href="{{ route('explore.index', ['type' => $key]) }}"
                   @if ($activeFilter === $key) aria-current="page" @endif
                   class="rounded-full border px-4 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 {{ $activeFilter === $key
                        ? 'border-teal-500 bg-teal-500 text-white'
                        : 'border-white/10 bg-white/5 text-slate-200 hover:border-teal-500/40 hover:bg-white/10 hover:text-teal-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        @if ($locations->isEmpty())
            <div class="mt-12 rounded-xl border border-white/10 bg-white/5 p-8 text-center sm:p-10">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-teal-300">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a.75.75 0 11-1.06 1.06l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-white">Nothing here yet</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                    No {{ $filters[$activeFilter] ?? 'destinations' }} have been added for this filter yet. Try another type or browse the full world.
                </p>
                <a href="{{ route('travel.index') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Browse destinations by country
                </a>
            </div>
        @else
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($locations as $location)
                    @include('frontend.partials.location-card', ['location' => $location])
                @endforeach
            </div>

            <div class="mt-8">
                {{ $locations->links() }}
            </div>
        @endif
    </section>
@endsection
