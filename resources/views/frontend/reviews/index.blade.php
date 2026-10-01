@extends('layouts.frontend')

@section('title', 'My reviews — '.config('app.name'))

@section('content')
    <section class="mx-auto max-w-3xl px-6 py-10 sm:py-14">
        <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">My reviews</h1>

        @if (session('status') === 'review-deleted')
            <div class="mt-6 rounded-lg border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-sm text-teal-300" role="status">
                Your review has been deleted.
            </div>
        @endif

        <div class="mt-8 space-y-4">
            @forelse ($reviews as $review)
                <article class="rounded-xl border border-white/10 bg-white/5 p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('travel.show', $review->location->full_slug) }}"
                               class="block truncate font-semibold text-white transition hover:text-teal-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                {{ $review->location->name }}
                            </a>

                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="flex items-center gap-0.5" role="img" aria-label="Rated {{ $review->rating }} out of 5">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <svg class="h-4 w-4 {{ $star <= $review->rating ? 'text-teal-400' : 'text-slate-600' }}"
                                             viewBox="0 0 20 20"
                                             fill="currentColor"
                                             aria-hidden="true">
                                            <path d="M10 1.5l2.6 5.27 5.82.85-4.21 4.1.99 5.79L10 14.77l-5.2 2.74.99-5.79L1.58 7.62l5.82-.85L10 1.5z"/>
                                        </svg>
                                    @endfor
                                </span>

                                @if ($review->status === \App\Enums\ReviewStatus::Approved)
                                    <span class="inline-flex items-center rounded-full border border-teal-500/30 bg-teal-500/10 px-2 py-0.5 font-medium text-teal-300">
                                        {{ $review->status->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 font-medium text-amber-300">
                                        {{ $review->status->label() }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('reviews.destroy', $review) }}"
                              onsubmit="return confirm('Delete this review? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-lg border border-white/10 px-3 py-1.5 text-xs font-medium text-rose-300 transition hover:bg-rose-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                                Delete
                            </button>
                        </form>
                    </div>

                    @if ($review->title)
                        <h2 class="mt-4 font-semibold text-white">{{ \Illuminate\Support\Str::limit($review->title, 80) }}</h2>
                    @endif

                    <p class="mt-2 text-sm leading-relaxed text-slate-300">{{ \Illuminate\Support\Str::limit($review->body, 180) }}</p>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-8 text-center">
                    <p class="mx-auto max-w-md text-sm text-slate-400">You haven't written any reviews yet.</p>
                    <a href="{{ route('search.index') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-lg border border-white/10 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
                        Find a place to review
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
    </section>
@endsection
