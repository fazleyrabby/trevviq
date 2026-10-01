<?php

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\Redirect;

it('lists active countries on the travel index page', function () {
    Location::factory()->country()->create(['name' => 'Zamunda']);

    $this->get(route('travel.index'))
        ->assertOk()
        ->assertSee('Zamunda');
});

it('renders a nested location page with its ancestor breadcrumb', function () {
    $country = Location::factory()->country()->create(['name' => 'Amazonia']);
    $city = Location::factory()->city()->childOf($country)->create(['name' => 'Brasilia']);
    $place = Location::factory()->type(LocationType::Beach)->childOf($city)->create(['name' => 'Copacabana']);

    $this->get(route('travel.show', $place->full_slug))
        ->assertOk()
        ->assertSee('Copacabana')
        ->assertSee('Brasilia')
        ->assertSee('Amazonia');
});

it('returns 404 for an unknown travel path', function () {
    $this->get(route('travel.show', 'does/not/exist'))->assertNotFound();
});

it('redirects a legacy travel path to its new path', function () {
    Redirect::create([
        'old_path' => '/travel/old-path',
        'new_path' => '/travel/new-path',
        'status_code' => 301,
    ]);

    $this->get('/travel/old-path')->assertRedirect('/travel/new-path');
});

it('marks a non-indexable location page as noindex', function () {
    $location = Location::factory()->type(LocationType::Beach)->create(['name' => 'Hidden Cove']);

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertSee('noindex');
});

it('does not mark an indexable location page as noindex', function () {
    $location = Location::factory()->type(LocationType::Beach)->create([
        'name' => 'Famous Cove',
        'indexable' => true,
    ]);

    $this->get(route('travel.show', $location->full_slug))
        ->assertOk()
        ->assertDontSee('noindex');
});

it('emits a canonical link to the location URL', function () {
    $location = Location::factory()->type(LocationType::Beach)->create(['name' => 'Canon Cove']);
    $url = route('travel.show', $location->full_slug);

    $this->get($url)
        ->assertOk()
        ->assertSee($url);
});
