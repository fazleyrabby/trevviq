@extends('layouts.frontend')

@section('title', 'Create an event — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-2xl px-6 py-10 sm:py-14">
        <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Create an event</h1>

        <p class="mt-2 text-sm text-slate-400">
            Events are tied to a location, so travellers browsing that place can find what is happening there. Add the dates, where to meet, and a link for details.
        </p>

        <form method="POST"
              action="{{ route('events.store') }}"
              class="mt-8 space-y-6 rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
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
                <p class="mt-1.5 text-xs text-slate-500">
                    Not sure of the ID? Find the place using
                    <a href="{{ route('search.index') }}" class="font-medium text-teal-300 underline underline-offset-2 transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">search</a>.
                </p>
                @error('location_id')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-200">Event name</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       maxlength="150"
                       class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="Sunset street food walk"/>
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-200">
                    Description <span class="font-normal text-slate-500">(optional)</span>
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                          placeholder="What is happening, who is it for, and what should people bring?">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-slate-200">Starts at</label>
                    <input type="datetime-local"
                           id="starts_at"
                           name="starts_at"
                           value="{{ old('starts_at') }}"
                           required
                           class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500"/>
                    @error('starts_at')
                        <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ends_at" class="block text-sm font-medium text-slate-200">
                        Ends at <span class="font-normal text-slate-500">(optional)</span>
                    </label>
                    <input type="datetime-local"
                           id="ends_at"
                           name="ends_at"
                           value="{{ old('ends_at') }}"
                           class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500"/>
                    @error('ends_at')
                        <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-slate-200">
                    Address <span class="font-normal text-slate-500">(optional)</span>
                </label>
                <input type="text"
                       id="address"
                       name="address"
                       value="{{ old('address') }}"
                       maxlength="255"
                       class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="Meeting point, street, or venue"/>
                @error('address')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="website_url" class="block text-sm font-medium text-slate-200">
                    Website <span class="font-normal text-slate-500">(optional)</span>
                </label>
                <input type="url"
                       id="website_url"
                       name="website_url"
                       value="{{ old('website_url') }}"
                       maxlength="255"
                       class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="https://example.com/event"/>
                @error('website_url')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Create event
                </button>

                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Back to events
                </a>
            </div>
        </form>
    </section>
@endsection
