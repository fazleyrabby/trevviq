<?php

use App\Enums\EventStatus;
use App\Enums\ReportStatus;
use App\Models\Event;
use App\Models\Location;
use App\Models\Report;
use App\Models\User;
use App\Services\Location\LocationContentService;

it('redirects a guest from the event creation form to login', function () {
    $this->get(route('events.create'))->assertRedirect(route('login'));
});

it('redirects a guest submitting an event to login', function () {
    $this->post(route('events.store'))->assertRedirect(route('login'));

    $this->assertDatabaseCount('events', 0);
});

it('lets an authenticated user create a published event', function () {
    config(['roam.events.auto_approve' => true]);

    $user = User::factory()->create();
    $location = Location::factory()->create();

    $response = $this->actingAs($user)->post(route('events.store'), [
        'location_id' => $location->id,
        'name' => 'Sunset street food walk',
        'description' => 'Meet at the old bridge and eat through the market.',
        'starts_at' => now()->addWeek()->toDateTimeString(),
        'ends_at' => now()->addWeek()->addHours(3)->toDateTimeString(),
        'address' => 'Old Bridge',
        'website_url' => 'https://example.com/event',
    ]);

    $event = Event::query()->sole();

    $response->assertRedirect(route('events.show', $event));

    expect($event->organizer_id)->toBe($user->id)
        ->and($event->location_id)->toBe($location->id)
        ->and($event->status)->toBe(EventStatus::Published)
        ->and($event->slug)->not->toBeEmpty();

    expect($location->fresh()->content_count)->toBe(1);
});

it('requires a name when creating an event', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('events.store'), [
        'location_id' => $location->id,
        'starts_at' => now()->addWeek()->toDateTimeString(),
    ])->assertSessionHasErrors('name');

    $this->assertDatabaseCount('events', 0);
});

it('requires a start time when creating an event', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('events.store'), [
        'location_id' => $location->id,
        'name' => 'A meetup without a date',
    ])->assertSessionHasErrors('starts_at');

    $this->assertDatabaseCount('events', 0);
});

it('requires the location to exist when creating an event', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('events.store'), [
        'location_id' => 999999,
        'name' => 'A meetup in nowhere',
        'starts_at' => now()->addWeek()->toDateTimeString(),
    ])->assertSessionHasErrors('location_id');

    $this->assertDatabaseCount('events', 0);
});

it('rejects an end time before the start time', function () {
    $user = User::factory()->create();
    $location = Location::factory()->create();

    $this->actingAs($user)->post(route('events.store'), [
        'location_id' => $location->id,
        'name' => 'A meetup that ends before it starts',
        'starts_at' => now()->addWeek()->toDateTimeString(),
        'ends_at' => now()->addWeek()->subHour()->toDateTimeString(),
    ])->assertSessionHasErrors('ends_at');

    $this->assertDatabaseCount('events', 0);
});

it('creates a pending event when auto approval is disabled', function () {
    config(['roam.events.auto_approve' => false]);

    $user = User::factory()->create();
    $location = Location::factory()->create();

    $response = $this->actingAs($user)->post(route('events.store'), [
        'location_id' => $location->id,
        'name' => 'Awaiting review gathering',
        'starts_at' => now()->addWeek()->toDateTimeString(),
    ]);

    $event = Event::query()->sole();

    $response->assertRedirect(route('events.show', $event));

    expect($event->status)->toBe(EventStatus::Pending)
        ->and($location->fresh()->content_count)->toBe(0);
});

it('generates a unique slug for events that share a name', function () {
    $first = Event::factory()->create(['name' => 'Harbour festival']);
    $second = Event::factory()->create(['name' => 'Harbour festival']);

    expect($first->slug)->not->toBe($second->slug)
        ->and($first->slug)->not->toBeEmpty()
        ->and($second->slug)->not->toBeEmpty();
});

it('forbids a non-owner from deleting an event', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $owner->id]);

    $this->actingAs($other)->delete(route('events.destroy', $event))->assertForbidden();

    $this->assertModelExists($event);
});

it('lets the owner delete their event and recomputes the location', function () {
    $owner = User::factory()->create();
    $location = Location::factory()->create();
    $event = Event::factory()->create([
        'organizer_id' => $owner->id,
        'location_id' => $location->id,
    ]);

    app(LocationContentService::class)->recompute($location);
    expect($location->fresh()->content_count)->toBe(1);

    $this->actingAs($owner)->delete(route('events.destroy', $event))
        ->assertRedirect(route('events.index'));

    $this->assertModelMissing($event);
    expect($location->fresh()->content_count)->toBe(0);
});

it('counts published events toward the location content count and indexation', function () {
    $location = Location::factory()->create();

    Event::factory()->count(3)->create(['location_id' => $location->id]);

    app(LocationContentService::class)->recompute($location);

    $location->refresh();

    expect($location->content_count)->toBe(3)
        ->and($location->indexable)->toBeTrue();
});

it('lets a user report an event once while the report is open', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)->post(route('events.report', $event), [
        'reason' => 'spam',
    ])->assertRedirect();

    $this->assertDatabaseHas('reports', [
        'reporter_id' => $user->id,
        'reportable_type' => Event::class,
        'reportable_id' => $event->id,
        'reason' => 'spam',
        'status' => ReportStatus::Open->value,
    ]);

    $this->actingAs($user)->post(route('events.report', $event), [
        'reason' => 'offensive',
    ])->assertRedirect()->assertSessionHas('status', 'report-duplicate');

    expect(Report::query()->count())->toBe(1);
});
