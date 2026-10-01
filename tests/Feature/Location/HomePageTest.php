<?php

use App\Models\Location;

it('renders the homepage hero', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Discover somewhere new.');
});

it('shows a popular city and country when data is present', function () {
    Location::factory()->city()->create(['name' => 'Marmalade City']);
    Location::factory()->country()->create(['name' => 'Jamboree Republic']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Marmalade City')
        ->assertSee('Jamboree Republic');
});

it('renders the empty states when the database is empty', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('No cities to show yet.')
        ->assertSee('Countries are being imported.');
});

it('posts the search form to the search index route', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('action="'.route('search.index').'"', escape: false);
});

it('links to the explore and travel index routes', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('explore.index'))
        ->assertSee(route('travel.index'));
});
