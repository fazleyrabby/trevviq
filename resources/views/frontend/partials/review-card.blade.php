@php
    $viewer = auth()->user();
    $isOwner = $review->isOwnedBy($viewer);
    $isHelpful = $review->isHelpfulBy($viewer);
    $isApproved = $review->status === \App\Enums\ReviewStatus::Approved;
    $buttonBase = 'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500';
@endphp

<article x-data="{ editing: false }" class="rounded-xl border border-white/10 bg-white/5 p-5 sm:p-6">
    <header class="flex items-start gap-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-500 text-sm font-bold text-white"
              aria-hidden="true">{{ $review->user->initials() }}</span>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <p class="font-semibold text-white">{{ $review->user->name }}</p>

                @unless ($isApproved)
                    <span class="inline-flex items-center rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-300">
                        {{ $review->status->label() }}
                    </span>
                @endunless
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
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

                @if ($review->visit_date)
                    <span aria-hidden="true" class="text-slate-600">&middot;</span>
                    <span>Visited {{ $review->visit_date->format('M Y') }}</span>
                @endif
            </div>
        </div>
    </header>

    @if ($review->title)
        <h3 class="mt-4 font-semibold text-white">{{ $review->title }}</h3>
    @endif

    <p class="mt-2 whitespace-pre-line leading-relaxed text-slate-300">{{ $review->body }}</p>

    <footer class="mt-5 flex flex-wrap items-center gap-3">
        @auth
            <form method="POST" action="{{ route('reviews.helpful', $review) }}">
                @csrf
                <button type="submit"
                        aria-pressed="{{ $isHelpful ? 'true' : 'false' }}"
                        class="{{ $buttonBase }} {{ $isHelpful ? 'border-teal-500/40 bg-teal-500/15 text-teal-200' : 'border-white/10 text-slate-300 hover:bg-white/5' }}">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M7 10.5V17H4a1 1 0 01-1-1v-5a1 1 0 011-1h3zm2-.9l3.1-6.2A1 1 0 0113 3a3 3 0 013 3v2h2.2a2 2 0 011.96 2.4l-1 5A2 2 0 0116.2 17H9V9.6z"/>
                    </svg>
                    Helpful ({{ $review->helpful_count }})
                </button>
            </form>
        @else
            <span class="{{ $buttonBase }} cursor-default border-white/10 text-slate-500" title="Log in to mark this review as helpful">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M7 10.5V17H4a1 1 0 01-1-1v-5a1 1 0 011-1h3zm2-.9l3.1-6.2A1 1 0 0113 3a3 3 0 013 3v2h2.2a2 2 0 011.96 2.4l-1 5A2 2 0 0116.2 17H9V9.6z"/>
                </svg>
                Helpful ({{ $review->helpful_count }})
            </span>
        @endauth

        @if ($isOwner)
            <button type="button"
                    class="{{ $buttonBase }} border-white/10 text-slate-300 hover:bg-white/5"
                    @click="editing = ! editing"
                    :aria-expanded="editing">
                <span x-show="! editing">Edit</span>
                <span x-show="editing" style="display: none;">Cancel</span>
            </button>

            <form method="POST"
                  action="{{ route('reviews.destroy', $review) }}"
                  onsubmit="return confirm('Delete this review? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="{{ $buttonBase }} border-white/10 text-rose-300 hover:bg-rose-500/10">
                    Delete
                </button>
            </form>
        @elseif (auth()->check())
            <details class="relative">
                <summary class="{{ $buttonBase }} cursor-pointer list-none border-white/10 text-slate-400 hover:bg-white/5">
                    Report
                </summary>

                <form method="POST"
                      action="{{ route('reviews.report', $review) }}"
                      class="absolute right-0 z-20 mt-2 w-64 space-y-3 rounded-xl border border-white/10 bg-slate-900 p-4 shadow-xl">
                    @csrf

                    <div>
                        <label for="report-reason-{{ $review->id }}" class="block text-xs font-medium text-slate-300">Reason</label>
                        <select id="report-reason-{{ $review->id }}"
                                name="reason"
                                required
                                class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950/60 px-2.5 py-1.5 text-xs text-slate-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            @foreach (\App\Enums\ReportReason::cases() as $reason)
                                <option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>{{ $reason->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="report-description-{{ $review->id }}" class="block text-xs font-medium text-slate-300">
                            Details <span class="font-normal text-slate-500">(optional)</span>
                        </label>
                        <textarea id="report-description-{{ $review->id }}"
                                  name="description"
                                  rows="2"
                                  maxlength="1000"
                                  class="mt-1 w-full rounded-lg border border-white/10 bg-slate-950/60 px-2.5 py-1.5 text-xs text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                        Report
                    </button>
                </form>
            </details>
        @endif
    </footer>

    @if ($isOwner)
        <div x-show="editing" style="display: none;" class="mt-5 border-t border-white/10 pt-5">
            @include('frontend.partials.review-form', ['location' => $review->location, 'review' => $review])
        </div>
    @endif
</article>
