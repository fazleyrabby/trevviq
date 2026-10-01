<?php

use App\Enums\VisitSource;
use App\Enums\VisitVerification;
use App\Models\Location;
use App\Models\TravellerLocation;
use App\Models\User;

it('redirects guests to login from the travel history endpoints', function () {
    $location = Location::factory()->create();

    $this->get(route('travel-history.index'))->assertRedirect(route('login'));

    $this->post(route('travel-history.store'), ['location_id' => $location->id])
        ->assertRedirect(route('login'));
});

it('lets an authenticated user add a visit', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)
        ->from(route('travel-history.index'))
        ->post(route('travel-history.store'), ['location_id' => $location->id])
        ->assertRedirect(route('travel-history.index'));

    $visit = TravellerLocation::query()
        ->where('user_id', $user->id)
        ->where('location_id', $location->id)
        ->sole();

    expect($visit->source)->toBe(VisitSource::Claimed)
        ->and($visit->verification_status)->toBe(VisitVerification::Unverified);
});

it('does not duplicate a visit to the same location', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)
        ->post(route('travel-history.store'), ['location_id' => $location->id]);
    $this->actingAs($user)
        ->post(route('travel-history.store'), ['location_id' => $location->id]);

    expect(TravellerLocation::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('requires the location to exist when adding a visit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('travel-history.store'), ['location_id' => 999999])
        ->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('traveller_locations', 0);
});

it('lets the owner remove a visit', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $visit = $user->visits()->create([
        'location_id' => $location->id,
        'source' => VisitSource::Claimed,
        'verification_status' => VisitVerification::Unverified,
    ]);

    $this->actingAs($user)
        ->delete(route('travel-history.destroy', $visit))
        ->assertRedirect();

    $this->assertModelMissing($visit);
});

it('forbids a user from removing someone else visit', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $location = Location::factory()->create();

    $visit = $owner->visits()->create([
        'location_id' => $location->id,
        'source' => VisitSource::Claimed,
        'verification_status' => VisitVerification::Unverified,
    ]);

    $this->actingAs($other)
        ->delete(route('travel-history.destroy', $visit))
        ->assertForbidden();

    $this->assertModelExists($visit);
});

it('lists the authenticated user visits on the index', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create(['name' => 'Barcelona']);

    $user->visits()->create([
        'location_id' => $location->id,
        'source' => VisitSource::Claimed,
        'verification_status' => VisitVerification::Unverified,
    ]);

    $this->actingAs($user)
        ->get(route('travel-history.index'))
        ->assertOk()
        ->assertSee('Barcelona');
});
