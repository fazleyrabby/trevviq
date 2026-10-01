<?php

use App\Enums\ReviewStatus;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;
use App\Services\Location\LocationContentService;

it('redirects a guest to login when submitting a review', function () {
    $location = Location::factory()->create();

    $this->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 5,
        'body' => 'A guest should never reach the create endpoint.',
    ])->assertRedirect(route('login'));

    $this->assertDatabaseCount('reviews', 0);
});

it('lets an authenticated user create a review', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();
    $body = 'I loved every minute of my stay in this quiet corner.';

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 5,
        'title' => 'A wonderful place',
        'body' => $body,
        'visit_date' => now()->subDay()->toDateString(),
    ])->assertRedirect(route('travel.show', $location->full_slug).'#reviews');

    $review = Review::query()
        ->where('user_id', $user->id)
        ->where('location_id', $location->id)
        ->sole();

    expect($review->status)->toBe(ReviewStatus::Approved)
        ->and($review->rating)->toBe(5)
        ->and($review->body)->toBe($body);

    expect($location->fresh()->content_count)->toBe(1);
});

it('rejects a rating outside the one to five range', function (int $rating) {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => $rating,
        'body' => 'A body that is comfortably long enough.',
    ])->assertSessionHasErrors('rating');

    $this->assertDatabaseCount('reviews', 0);
})->with([
    'zero' => [0],
    'six' => [6],
]);

it('rejects a body shorter than ten characters', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 4,
        'body' => 'too short',
    ])->assertSessionHasErrors('body');

    $this->assertDatabaseCount('reviews', 0);
});

it('requires the location to exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => 999999,
        'rating' => 4,
        'body' => 'A body that passes the length rule.',
    ])->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('reviews', 0);
});

it('enforces the review cooldown per location', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();
    $existing = Review::factory()->for($user)->for($location)->create();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 4,
        'body' => 'A second review inside the cooldown window.',
    ])->assertSessionHasErrors('location_id');

    $existing->forceFill(['created_at' => now()->subDays(31)])->save();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 4,
        'body' => 'A fresh review once the cooldown has passed.',
    ])->assertRedirect(route('travel.show', $location->full_slug).'#reviews');

    expect(Review::query()->where('user_id', $user->id)->count())->toBe(2);
});

it('creates a pending review when auto approval is disabled', function () {
    config(['roam.reviews.auto_approve' => false]);

    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('reviews.store'), [
        'location_id' => $location->id,
        'rating' => 5,
        'body' => 'This review should land in the moderation queue.',
    ])->assertRedirect(route('travel.show', $location->full_slug).'#reviews');

    $review = Review::query()->where('user_id', $user->id)->sole();

    expect($review->status)->toBe(ReviewStatus::Pending)
        ->and($location->fresh()->content_count)->toBe(0);
});

it('marks a location indexable once approved reviews reach the threshold', function () {
    $location = Location::factory()->create();
    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        $this->actingAs($user)->post(route('reviews.store'), [
            'location_id' => $location->id,
            'rating' => 5,
            'body' => 'A genuinely helpful review body for travellers.',
        ])->assertRedirect(route('travel.show', $location->full_slug).'#reviews');
    }

    $location->refresh();

    expect($location->content_count)->toBe(3)
        ->and($location->indexable)->toBeTrue();
});

it('lets the owner update their review', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();
    $review = Review::factory()->for($user)->for($location)->create([
        'rating' => 3,
        'body' => 'The original review body before editing.',
    ]);

    $this->actingAs($user)->put(route('reviews.update', $review), [
        'rating' => 5,
        'title' => 'Updated title',
        'body' => 'An updated review body that is long enough.',
    ])->assertRedirect(route('travel.show', $location->full_slug).'#reviews');

    $review->refresh();

    expect($review->rating)->toBe(5)
        ->and($review->body)->toBe('An updated review body that is long enough.');
});

it('forbids a non-owner from updating or deleting a review', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $review = Review::factory()->for($owner)->create();

    $this->actingAs($other)->put(route('reviews.update', $review), [
        'rating' => 1,
        'body' => 'Trying to overwrite someone else review.',
    ])->assertForbidden();

    $this->actingAs($other)->delete(route('reviews.destroy', $review))->assertForbidden();

    $this->assertModelExists($review);
});

it('lets the owner delete their review and recomputes the location', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();
    $review = Review::factory()->for($user)->for($location)->create();

    app(LocationContentService::class)->recompute($location);
    expect($location->fresh()->content_count)->toBe(1);

    $this->actingAs($user)->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('travel.show', $location->full_slug).'#reviews');

    $this->assertModelMissing($review);
    expect($location->fresh()->content_count)->toBe(0);
});
