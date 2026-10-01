<?php

use App\Models\Review;
use App\Models\User;

it('redirects guests to login from my reviews', function () {
    $this->get(route('reviews.index'))->assertRedirect(route('login'));
});

it('shows only the authenticated user reviews', function () {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $actorBody = 'The review body written by the signed in traveller.';
    $otherBody = 'A review body belonging to somebody else entirely.';

    Review::factory()->for($actor)->create(['body' => $actorBody]);
    Review::factory()->for($other)->create(['body' => $otherBody]);

    $this->actingAs($actor)
        ->get(route('reviews.index'))
        ->assertOk()
        ->assertSee($actorBody)
        ->assertDontSee($otherBody);
});

it('lists a pending review with its status label', function () {
    $user = User::factory()->create();

    Review::factory()->pending()->for($user)->create([
        'body' => 'This review is waiting for a moderator to look at it.',
    ]);

    $this->actingAs($user)
        ->get(route('reviews.index'))
        ->assertOk()
        ->assertSee('Pending review');
});
