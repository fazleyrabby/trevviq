<?php

use App\Models\User;

it('lets an authenticated user follow another traveller', function () {
    $follower = User::factory()->create();
    $traveller = User::factory()->create();

    $this->actingAs($follower)
        ->from(route('traveller.show', $traveller->username))
        ->post(route('follows.store', $traveller))
        ->assertRedirect(route('traveller.show', $traveller->username));

    $this->assertDatabaseHas('follows', [
        'follower_id' => $follower->id,
        'following_id' => $traveller->id,
    ]);
});

it('does not create a duplicate follow row', function () {
    $follower = User::factory()->create();
    $traveller = User::factory()->create();

    $this->actingAs($follower)->post(route('follows.store', $traveller));
    $this->actingAs($follower)->post(route('follows.store', $traveller));

    $this->assertDatabaseCount('follows', 1);
});

it('lets a user unfollow a traveller', function () {
    $follower = User::factory()->create();
    $traveller = User::factory()->create();

    $follower->following()->attach($traveller->id);

    $this->actingAs($follower)
        ->delete(route('follows.destroy', $traveller))
        ->assertRedirect();

    $this->assertDatabaseMissing('follows', [
        'follower_id' => $follower->id,
        'following_id' => $traveller->id,
    ]);
});

it('does not let a user follow themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('traveller.show', $user->username))
        ->post(route('follows.store', $user))
        ->assertRedirect(route('traveller.show', $user->username));

    $this->assertDatabaseCount('follows', 0);
});

it('redirects guests to login when following', function () {
    $traveller = User::factory()->create();

    $this->post(route('follows.store', $traveller))->assertRedirect(route('login'));
});
