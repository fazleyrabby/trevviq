<?php

use App\Enums\EventStatus;
use App\Models\Admin;
use App\Models\Event;
use App\Models\Location;

it('approves a pending event and recomputes the location content count', function () {
    $admin = Admin::factory()->create();
    $location = Location::factory()->create();
    $event = Event::factory()->pending()->create(['location_id' => $location->id]);

    $this->actingAs($admin, 'admin')
        ->from(route('admin.events.index'))
        ->patch(route('admin.events.update', $event), ['status' => 'published'])
        ->assertRedirect(route('admin.events.index'));

    expect($event->fresh()->status)->toBe(EventStatus::Published)
        ->and($location->fresh()->content_count)->toBe(1);
});

it('rejects a pending event', function () {
    $admin = Admin::factory()->create();
    $location = Location::factory()->create();
    $event = Event::factory()->pending()->create(['location_id' => $location->id]);

    $this->actingAs($admin, 'admin')
        ->from(route('admin.events.index'))
        ->patch(route('admin.events.update', $event), ['status' => 'rejected'])
        ->assertRedirect(route('admin.events.index'));

    expect($event->fresh()->status)->toBe(EventStatus::Rejected)
        ->and($location->fresh()->content_count)->toBe(0);
});

it('redirects guests from the admin event queue to the admin login', function () {
    $this->get(route('admin.events.index'))
        ->assertRedirect(route('admin.login'));
});
