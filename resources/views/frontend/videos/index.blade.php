@extends('layouts.frontend')

@section('title', 'Traveller videos — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-10 sm:py-14">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Traveller videos</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-400">
                    Real places, seen by the people who went. Watch 60-second stories from travellers around the world.
                </p>
            </div>

            @auth
                <a href="{{ route('videos.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Share a video
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-teal-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                    Share a video
                </a>
            @endauth
        </div>

        @if (in_array(session('status'), ['video-uploaded', 'video-deleted', 'video-shared'], true))
            <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                @switch(session('status'))
                    @case('video-uploaded')
                        Thanks &mdash; your video has been uploaded and is being processed.
                        @break
                    @case('video-deleted')
                        Your video has been deleted.
                        @break
                    @case('video-shared')
                        Thanks for sharing.
                        @break
                @endswitch
            </div>
        @endif

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($videos as $video)
                @include('frontend.partials.video-card', ['video' => $video])
            @empty
                <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center sm:col-span-2 lg:col-span-3">
                    <p class="mx-auto max-w-md text-sm text-slate-400">
                        No videos yet &mdash; be the first to share one.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $videos->links() }}
        </div>
    </section>
@endsection
