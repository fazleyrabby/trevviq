<?php

use App\Models\Location;
use App\Models\Review;
use App\Models\User;

it('shows an approved review body and its author name', function () {
    $location = Location::factory()->create();
    $author = User::factory()->create(['name' => 'Reviewer Alice']);
    $body = 'A wonderfully distinctive review body about the harbour at dusk.';

    Review::factory()->create([
        'location_id' => $location->id,
        'user_id' => $author->id,
        'body' => $body,
    ]);

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertSee($body)
        ->assertSee('Reviewer Alice');
});

it('shows the review summary average and count', function () {
    $location = Location::factory()->create();
    $first = User::factory()->create(['name' => 'Summary Author One']);
    $second = User::factory()->create(['name' => 'Summary Author Two']);

    Review::factory()->create([
        'location_id' => $location->id,
        'user_id' => $first->id,
        'rating' => 5,
        'body' => 'Five star experience at the quiet lagoon.',
    ]);

    Review::factory()->create([
        'location_id' => $location->id,
        'user_id' => $second->id,
        'rating' => 3,
        'body' => 'A solid three star stay near the old town.',
    ]);

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertSee('Summary Author One')
        ->assertSee('Five star experience at the quiet lagoon.')
        ->assertSee('2 reviews');
});

it('does not show a pending review body to a guest', function () {
    $location = Location::factory()->create();
    $body = 'This pending review body must remain hidden from the public.';

    Review::factory()->pending()->create([
        'location_id' => $location->id,
        'body' => $body,
    ]);

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertDontSee($body);
});

it('prompts a guest to log in to write a review', function () {
    $location = Location::factory()->create();

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertSee(route('login'));
});

it('shows the write-a-review form to an authenticated user without a review', function () {
    $location = Location::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertSee(route('reviews.store'));
});
