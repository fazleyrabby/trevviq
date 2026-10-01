<?php

use App\Models\Review;
use App\Models\ReviewHelpful;
use App\Models\User;

it('marks a review as helpful', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->actingAs($user)->post(route('reviews.helpful', $review))->assertRedirect();

    $this->assertDatabaseHas('review_helpfuls', [
        'review_id' => $review->id,
        'user_id' => $user->id,
    ]);

    expect($review->fresh()->helpful_count)->toBe(1);
});

it('toggles an existing helpful mark off', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();
    ReviewHelpful::create([
        'review_id' => $review->id,
        'user_id' => $user->id,
    ]);
    $review->forceFill(['helpful_count' => 1])->save();

    $this->actingAs($user)->post(route('reviews.helpful', $review))->assertRedirect();

    $this->assertDatabaseMissing('review_helpfuls', [
        'review_id' => $review->id,
        'user_id' => $user->id,
    ]);

    expect($review->fresh()->helpful_count)->toBe(0);
});

it('redirects a guest to login', function () {
    $review = Review::factory()->create();

    $this->post(route('reviews.helpful', $review))->assertRedirect(route('login'));

    $this->assertDatabaseCount('review_helpfuls', 0);
});

it('counts helpful marks from different users', function () {
    $review = Review::factory()->create();
    $first = User::factory()->create();
    $second = User::factory()->create();

    $this->actingAs($first)->post(route('reviews.helpful', $review))->assertRedirect();
    $this->actingAs($second)->post(route('reviews.helpful', $review))->assertRedirect();

    $this->assertDatabaseCount('review_helpfuls', 2);
    expect($review->fresh()->helpful_count)->toBe(2);
});
