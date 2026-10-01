<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Location;
use App\Models\Review;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $location = Location::query()->findOrFail($request->integer('location_id'));

        Gate::authorize('create', Review::class);

        $status = config('roam.reviews.auto_approve')
            ? ReviewStatus::Approved
            : ReviewStatus::Pending;

        $validated = $request->validated();

        $request->user()->reviews()->create([
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'visit_date' => $validated['visit_date'] ?? null,
            'location_id' => $location->id,
            'status' => $status,
        ]);

        app(LocationContentService::class)->recompute($location);

        return redirect()
            ->route('travel.show', $location->full_slug)
            ->withFragment('reviews')
            ->with('status', 'review-submitted');
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($request->validated());

        app(LocationContentService::class)->recompute($review->location);

        return redirect()
            ->route('travel.show', $review->location->full_slug)
            ->withFragment('reviews')
            ->with('status', 'review-updated');
    }

    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $location = $review->location;

        $review->delete();

        app(LocationContentService::class)->recompute($location);

        return redirect()
            ->route('travel.show', $location->full_slug)
            ->withFragment('reviews')
            ->with('status', 'review-deleted');
    }
}
