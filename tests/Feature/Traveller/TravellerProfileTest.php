<?php

use App\Enums\VisitSource;
use App\Enums\VisitVerification;
use App\Models\Location;
use App\Models\Review;
use App\Models\User;

it('renders a public traveller profile by username', function () {
    $user = User::factory()->create(['name' => 'Profile Owner']);

    $this->get(route('traveller.show', $user->username))
        ->assertOk()
        ->assertSee('Profile Owner');
});

it('returns a 404 for an unknown username', function () {
    $this->get(route('traveller.show', 'no-such-traveller'))
        ->assertNotFound();
});

it('shows the traveller stats and their approved reviews', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $location = Location::factory()->create();

    $user->visits()->create([
        'location_id' => $location->id,
        'visited_at' => now()->subMonth(),
        'source' => VisitSource::Claimed,
        'verification_status' => VisitVerification::Unverified,
    ]);

    $body = 'A distinctive review body used to prove the reviews section renders.';

    Review::factory()->for($user)->count(2)->create(['body' => $body]);

    $follower->following()->attach($user->id);

    $this->get(route('traveller.show', $user->username))
        ->assertOk()
        ->assertSee($body)
        ->assertSee('<dd class="mt-1 text-lg font-semibold text-white">1</dd>', false)
        ->assertSee('<dd class="mt-1 text-lg font-semibold text-white">2</dd>', false)
        ->assertSee('<dd class="mt-1 text-lg font-semibold text-white">0</dd>', false);
});

it('shows only approved reviews on the profile', function () {
    $user = User::factory()->create();
    $approvedBody = 'The approved review body that every guest is allowed to read.';
    $pendingBody = 'The pending review body that must stay private to moderators.';

    Review::factory()->for($user)->create(['body' => $approvedBody]);
    Review::factory()->pending()->for($user)->create(['body' => $pendingBody]);

    $this->get(route('traveller.show', $user->username))
        ->assertOk()
        ->assertSee($approvedBody)
        ->assertDontSee($pendingBody);
});

it('prompts a guest to log in from the profile', function () {
    $user = User::factory()->create();

    $this->get(route('traveller.show', $user->username))
        ->assertOk()
        ->assertSee(route('login'));
});

it('shows the owner an edit link instead of a follow form on their own profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('traveller.show', $user->username))
        ->assertOk()
        ->assertSee('Edit profile')
        ->assertSee(route('profile.edit'))
        ->assertDontSee(route('follows.store', $user));
});

it('shows the unfollow action to a viewer who already follows the traveller', function () {
    $traveller = User::factory()->create();
    $viewer = User::factory()->create();

    $viewer->following()->attach($traveller->id);

    $this->actingAs($viewer)
        ->get(route('traveller.show', $traveller->username))
        ->assertOk()
        ->assertSee('Following')
        ->assertSee(route('follows.destroy', $traveller));
});
