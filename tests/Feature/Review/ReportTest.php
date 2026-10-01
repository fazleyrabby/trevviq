<?php

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;

it('lets an authenticated user report a review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->actingAs($user)->post(route('reviews.report', $review), [
        'reason' => 'spam',
    ])->assertRedirect();

    $this->assertDatabaseHas('reports', [
        'reporter_id' => $user->id,
        'reportable_type' => Review::class,
        'reportable_id' => $review->id,
        'reason' => 'spam',
        'status' => ReportStatus::Open->value,
    ]);
});

it('does not create a duplicate report while one is still open', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();
    $back = route('travel.show', $review->location->full_slug);

    $this->actingAs($user)->from($back)->post(route('reviews.report', $review), [
        'reason' => 'spam',
    ])->assertRedirect($back.'#reviews');

    $this->actingAs($user)->from($back)->post(route('reviews.report', $review), [
        'reason' => 'offensive',
    ])->assertRedirect($back.'#reviews')->assertSessionHas('status', 'report-duplicate');

    expect(Report::query()->count())->toBe(1);
});

it('rejects an invalid report reason', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->actingAs($user)->post(route('reviews.report', $review), [
        'reason' => 'nonsense',
    ])->assertSessionHasErrors('reason');

    $this->assertDatabaseCount('reports', 0);
});

it('redirects a guest to login', function () {
    $review = Review::factory()->create();

    $this->post(route('reviews.report', $review), [
        'reason' => 'spam',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseCount('reports', 0);
});
