@extends('layouts.frontend')

@section('title', 'Events — '.config('app.name'))
@section('description', 'Discover upcoming events around the world, organised by travellers who know these places best.')
@section('canonical', route('events.index'))

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 sm:py-14">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Events</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Meet-ups, festivals, and gatherings tied to the places travellers love. Find something happening near your next stop.
                </p>
            </div>

            @auth
                <a href="{{ route('events.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Create event
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Create event
                </a>
            @endauth
        </div>

        @if (in_array(session('status'), ['event-created', 'event-deleted'], true))
            <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                @switch(session('status'))
                    @case('event-created')
                        Thanks &mdash; your event has been created.
                        @break
                    @case('event-deleted')
                        Your event has been deleted.
                        @break
                @endswitch
            </div>
        @endif

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($events as $event)
                @include('frontend.partials.event-card', ['event' => $event])
            @empty
                <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center sm:col-span-2 lg:col-span-3">
                    <p class="mx-auto max-w-md text-sm text-slate-400">
                        No upcoming events yet.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </section>
@endsection
