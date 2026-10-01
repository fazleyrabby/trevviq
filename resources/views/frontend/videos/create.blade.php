@extends('layouts.frontend')

@section('title', 'Share a video — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-2xl px-6 py-10 sm:py-14">
        <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Share a video</h1>

        <p class="mt-2 text-sm text-slate-400">
            Videos must be 60 seconds or less, up to 100 MB, in MP4 or MOV format. Only upload footage you made yourself.
        </p>

        <form method="POST"
              action="{{ route('videos.store') }}"
              enctype="multipart/form-data"
              class="mt-8 space-y-6 rounded-xl border border-white/10 bg-white/5 p-6 sm:p-8">
            @csrf

            <div>
                <label for="video" class="block text-sm font-medium text-slate-200">Video file</label>
                <input type="file"
                       id="video"
                       name="video"
                       accept="video/mp4,video/quicktime"
                       required
                       class="mt-1.5 block w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 file:mr-3 file:rounded-md file:border-0 file:bg-teal-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500"/>
                @error('video')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

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
                <label for="title" class="block text-sm font-medium text-slate-200">
                    Title <span class="font-normal text-slate-500">(optional)</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title') }}"
                       maxlength="150"
                       class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       placeholder="Sunset at Patenga Beach"/>
                @error('title')
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
                          placeholder="What made this moment worth sharing?">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="visibility" class="block text-sm font-medium text-slate-200">Visibility</label>
                <select id="visibility"
                        name="visibility"
                        class="mt-1.5 w-full rounded-lg border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @foreach (\App\Enums\VideoVisibility::cases() as $visibility)
                        <option value="{{ $visibility->value }}" @selected(old('visibility', \App\Enums\VideoVisibility::Public->value) === $visibility->value)>
                            {{ $visibility->label() }}
                        </option>
                    @endforeach
                </select>
                @error('visibility')
                    <p class="mt-1.5 text-xs text-rose-300" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Upload video
                </button>

                <a href="{{ route('videos.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                    Back to videos
                </a>
            </div>
        </form>
    </section>
@endsection
