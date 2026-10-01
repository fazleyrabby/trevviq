<?php

use App\Enums\ReviewStatus;
use App\Models\Location;
use App\Models\Review;

it('approves a pending review and recomputes the location content count', function () {
    $location = Location::factory()->create();
    $review = Review::factory()->pending()->create(['location_id' => $location->id]);

    $this->artisan('reviews:moderate', [
        'review' => $review->id,
        '--status' => 'approved',
    ])->assertExitCode(0);

    expect($review->fresh()->status)->toBe(ReviewStatus::Approved)
        ->and($location->fresh()->content_count)->toBe(1);
});

it('rejects a review and clears the location content count', function () {
    $location = Location::factory()->create();
    $review = Review::factory()->pending()->create(['location_id' => $location->id]);

    $this->artisan('reviews:moderate', [
        'review' => $review->id,
        '--status' => 'rejected',
    ])->assertExitCode(0);

    expect($review->fresh()->status)->toBe(ReviewStatus::Rejected)
        ->and($location->fresh()->content_count)->toBe(0);
});

it('fails on an invalid status', function () {
    $review = Review::factory()->pending()->create();

    $this->artisan('reviews:moderate', [
        'review' => $review->id,
        '--status' => 'bogus',
    ])->assertExitCode(1);

    expect($review->fresh()->status)->toBe(ReviewStatus::Pending);
});

it('fails on a non-existent review id', function () {
    $this->artisan('reviews:moderate', [
        'review' => 999999,
        '--status' => 'approved',
    ])->assertExitCode(1);
});
