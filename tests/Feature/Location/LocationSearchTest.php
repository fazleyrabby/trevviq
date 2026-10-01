<?php

use App\Models\Location;

it('renders the search prompt when no query is given', function () {
    $this->get(route('search.index'))
        ->assertOk()
        ->assertSee('Search destinations worldwide');
});

it('finds a location by name', function () {
    $paris = Location::factory()->city()->create(['name' => 'Paris']);

    $this->get(route('search.index', ['q' => 'Paris']))
        ->assertOk()
        ->assertSee('>Paris</span>', escape: false)
        ->assertSee('1 result for');
});

it('ranks an exact name match ahead of a prefix match', function () {
    Location::factory()->city()->create(['name' => 'Paris']);
    Location::factory()->city()->create(['name' => 'Parisville']);

    $this->get(route('search.index', ['q' => 'paris']))
        ->assertOk()
        ->assertSeeInOrder(['>Paris</span>', '>Parisville</span>'], escape: false);
});

it('matches across the hierarchy using the chained search index', function () {
    $france = Location::factory()->country()->create(['name' => 'France']);
    Location::factory()->city()->childOf($france)->create(['name' => 'Paris']);

    $this->get(route('search.index', ['q' => 'paris france']))
        ->assertOk()
        ->assertSee('>Paris</span>', escape: false)
        ->assertSee('1 result for');
});

it('filters results by location type', function () {
    Location::factory()->country()->create(['name' => 'Testlandia']);
    Location::factory()->city()->create(['name' => 'Testville']);

    $this->get(route('search.index', ['q' => 'test', 'type' => 'city']))
        ->assertOk()
        ->assertSee('>Testville</span>', escape: false)
        ->assertDontSee('>Testlandia</span>', escape: false);
});

it('shows the empty state when nothing matches', function () {
    $this->get(route('search.index', ['q' => 'zzzzznotfound']))
        ->assertOk()
        ->assertSee('No places found');
});
