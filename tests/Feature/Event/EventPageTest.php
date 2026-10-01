<?php

use App\Models\Event;
use App\Models\User;

it('shows published upcoming events on the public index', function () {
    Event::factory()->create(['name' => 'Trevviq public meetup']);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSee('Trevviq public meetup');
});

it('excludes pending and past events from the index', function () {
    Event::factory()->create(['name' => 'Upcoming visible event']);
    Event::factory()->pending()->create(['name' => 'Pending hidden event']);
    Event::factory()->past()->create(['name' => 'Past hidden event']);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSee('Upcoming visible event')
        ->assertDontSee('Pending hidden event')
        ->assertDontSee('Past hidden event');
});

it('shows a published event to a guest', function () {
    $event = Event::factory()->create(['name' => 'Guest visible event']);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Guest visible event');
});

it('hides a pending event from a non-owner but shows it to the owner', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->pending()->create([
        'name' => 'Owner only event',
        'organizer_id' => $owner->id,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertNotFound();

    $this->actingAs($owner)
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Owner only event');
});
