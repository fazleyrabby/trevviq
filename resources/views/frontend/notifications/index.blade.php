@extends('layouts.frontend')

@section('title', 'Notifications — '.config('app.name'))
@section('canonical', route('notifications.index'))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Notifications</h1>
            @if ($notifications->where('read_at', null)->count() > 0)
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-teal-400 hover:text-teal-300">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-8 space-y-4">
            @forelse ($notifications as $notification)
                <div class="relative flex items-start gap-4 rounded-xl border {{ $notification->read_at ? 'border-white/5 bg-white/5 opacity-75' : 'border-teal-500/20 bg-teal-500/5' }} p-5 transition hover:bg-white/10">
                    <div class="mt-1">
                        @if ($notification->type === 'App\Notifications\NewFollower')
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-500/20 text-teal-300">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/>
                                </svg>
                            </span>
                        @else
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-500/20 text-rose-300">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-slate-200">
                            @if ($notification->type === 'App\Notifications\NewFollower')
                                <a href="{{ route('traveller.show', $notification->data['follower_username']) }}" class="font-semibold hover:underline">
                                    {{ $notification->data['follower_name'] }}
                                </a> started following you.
                            @elseif ($notification->type === 'App\Notifications\VideoLiked')
                                <a href="{{ route('traveller.show', $notification->data['liker_username']) }}" class="font-semibold hover:underline">
                                    {{ $notification->data['liker_name'] }}
                                </a> liked your video
                                <a href="{{ route('videos.show', $notification->data['video_id']) }}" class="font-semibold hover:underline">
                                    {{ $notification->data['video_title'] ?? 'video' }}
                                </a>.
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if (!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.markRead', $notification->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-slate-400 hover:text-white" aria-label="Mark as read">
                                <span class="sr-only">Mark as read</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center text-sm text-slate-500">
                    You have no notifications yet.
                </p>
            @endforelse

            {{ $notifications->links() }}
        </div>
    </section>
@endsection
