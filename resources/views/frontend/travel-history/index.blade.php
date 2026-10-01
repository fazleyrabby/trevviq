@extends('layouts.frontend')

@section('title', 'Travel history — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
        <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Travel history</h1>

        @if (session('status') === 'visit-added')
            <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                Place marked as visited.
            </div>
        @elseif (session('status') === 'visit-removed')
            <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                Visit removed from your travel history.
            </div>
        @endif

        <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-white">Mark a place as visited</h2>
            <p class="mt-1.5 text-sm text-slate-400">
                Find a place using
                <a href="{{ route('search.index') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">search</a>
                and add its place ID below to build your travel history.
            </p>

            <form method="POST" action="{{ route('travel-history.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf

                <div>
                    <label for="location_id" class="block text-sm font-medium text-slate-200">Place ID</label>
                    <input type="number"
                           id="location_id"
                           name="location_id"
                           value="{{ old('location_id') }}"
                           required
                           inputmode="numeric"
                           min="1"
                           class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"/>
                    @error('location_id')
                        <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="visited_at" class="block text-sm font-medium text-slate-200">
                        Date visited <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input type="date"
                           id="visited_at"
                           name="visited_at"
                           value="{{ old('visited_at') }}"
                           max="{{ now()->toDateString() }}"
                           class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500"/>
                    @error('visited_at')
                        <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Mark as visited
                    </button>
                </div>
            </form>
        </div>

        <section class="mt-12" aria-labelledby="visits-heading">
            <h2 id="visits-heading" class="text-lg font-semibold text-white">Places you've marked</h2>

            <ul class="mt-4 space-y-3">
                @forelse ($visits as $visit)
                    <li class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/5 p-5">
                        <div class="min-w-0">
                            <a href="{{ route('travel.show', $visit->location->full_slug) }}"
                               class="block truncate font-semibold text-white transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                {{ $visit->location->name }}
                            </a>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                @if ($visit->visited_at)
                                    <span>Visited {{ $visit->visited_at->format('M Y') }}</span>
                                @endif
                                <span class="inline-flex items-center rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 font-medium text-slate-300">
                                    {{ $visit->verification_status->label() }}
                                </span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('travel-history.destroy', $visit) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                Remove
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                        <p class="mx-auto max-w-md text-sm text-slate-400">You haven't marked any places yet.</p>
                    </li>
                @endforelse
            </ul>
        </section>
    </section>
@endsection
