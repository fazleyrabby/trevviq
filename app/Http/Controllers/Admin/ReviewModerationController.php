<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    /**
     * Display the review moderation queue.
     */
    public function index(Request $request): View
    {
        $statusQuery = $request->query('status');
        $statusEnum = ReviewStatus::tryFrom((string) $statusQuery);

        $reviews = Review::query()
            ->with(['user', 'location'])
            ->when($statusEnum, fn ($query) => $query->where('status', $statusEnum))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'status' => is_string($statusQuery) ? $statusQuery : null,
            'statuses' => ReviewStatus::cases(),
        ]);
    }

    /**
     * Update the status of a review.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(ReviewStatus::class)],
        ]);

        $review->update(['status' => $data['status']]);

        if ($review->location) {
            app(LocationContentService::class)->recompute($review->location);
        }

        return back()->with('success', 'Review status updated.');
    }
}
