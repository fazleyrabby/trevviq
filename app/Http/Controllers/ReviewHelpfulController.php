<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewHelpful;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewHelpfulController extends Controller
{
    public function toggle(Request $request, Review $review): RedirectResponse
    {
        DB::transaction(function () use ($request, $review): void {
            $existing = ReviewHelpful::query()
                ->where('user_id', $request->user()->id)
                ->where('review_id', $review->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
            } else {
                $review->helpfuls()->create(['user_id' => $request->user()->id]);
            }

            $review->forceFill(['helpful_count' => $review->helpfuls()->count()])->save();
        });

        return back()->withFragment('reviews');
    }
}
