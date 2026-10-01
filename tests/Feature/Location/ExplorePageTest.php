<?php

use App\Enums\LocationType;
use App\Models\Location;

it('defaults to showing cities and not countries', function () {
    Location::factory()->city()->create(['name' => 'Cityvania']);
    Location::factory()->country()->create(['name' => 'Countryzania']);

    $this->get(route('explore.index'))
        ->assertOk()
        ->assertSee('Cityvania')
        ->assertDontSee('Countryzania');
});

it('filters to countries with the country type', function () {
    Location::factory()->city()->create(['name' => 'Cityvania']);
    Location::factory()->country()->create(['name' => 'Countryzania']);

    $this->get(route('explore.index', ['type' => 'country']))
        ->assertOk()
        ->assertSee('Countryzania')
        ->assertDontSee('Cityvania');
});

it('filters to points of interest with the place type', function () {
    Location::factory()->type(LocationType::Attraction)->create(['name' => 'Attractionland']);
    Location::factory()->city()->create(['name' => 'Cityvania']);

    $this->get(route('explore.index', ['type' => 'place']))
        ->assertOk()
        ->assertSee('Attractionland')
        ->assertDontSee('Cityvania');
});

it('falls back to cities for an unknown type', function () {
    Location::factory()->city()->create(['name' => 'Cityvania']);
    Location::factory()->country()->create(['name' => 'Countryzania']);

    $this->get(route('explore.index', ['type' => 'bogus']))
        ->assertOk()
        ->assertSee('Cityvania')
        ->assertDontSee('Countryzania');
});

it('renders an empty state when the database is empty', function () {
    $this->get(route('explore.index'))
        ->assertOk()
        ->assertSee('Nothing here yet');
});
