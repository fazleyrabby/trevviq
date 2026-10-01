<?php

use App\Enums\LocationType;
use App\Models\Location;

it('relates a child to its parent and a parent to its children', function () {
    $parent = Location::factory()->country()->create();
    $child = Location::factory()->city()->childOf($parent)->create();

    expect($child->parent->id)->toBe($parent->id)
        ->and($child->parent->is($parent))->toBeTrue()
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

it('computes depth from the parent chain', function () {
    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();
    $place = Location::factory()->type(LocationType::Attraction)->childOf($city)->create();

    expect($country->depth)->toBe(0)
        ->and($city->depth)->toBe(1)
        ->and($place->depth)->toBe(2);
});

it('returns ancestors root to parent and appends itself last in breadcrumbs', function () {
    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();
    $place = Location::factory()->type(LocationType::Attraction)->childOf($city)->create();

    expect($place->ancestors()->pluck('id')->all())->toBe([$country->id, $city->id])
        ->and($place->breadcrumbs()->pluck('id')->all())->toBe([$country->id, $city->id, $place->id])
        ->and($place->breadcrumbs()->last()->id)->toBe($place->id);
});

it('chains ancestor names and its own name into the search index', function () {
    $country = Location::factory()->country()->create(['name' => 'France']);
    $city = Location::factory()->city()->childOf($country)->create(['name' => 'Paris']);

    expect($city->search_index)->toContain('France')
        ->and($city->search_index)->toContain('Paris');
});

it('resolves routes by the full slug', function () {
    expect((new Location)->getRouteKeyName())->toBe('full_slug');
});

it('filters active locations', function () {
    $active = Location::factory()->create(['is_active' => true]);
    $inactive = Location::factory()->create(['is_active' => false]);

    $ids = Location::query()->active()->pluck('id');

    expect($ids)->toContain($active->id)->not->toContain($inactive->id);
});

it('filters indexable locations', function () {
    $indexable = Location::factory()->create(['indexable' => true]);
    $blocked = Location::factory()->create(['indexable' => false]);

    $ids = Location::query()->indexable()->pluck('id');

    expect($ids)->toContain($indexable->id)->not->toContain($blocked->id);
});

it('filters by one or more location types', function () {
    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->create();
    $district = Location::factory()->district()->create();

    expect(Location::query()->ofType(LocationType::City)->pluck('id')->all())->toBe([$city->id]);

    $multi = Location::query()->ofType(LocationType::City, LocationType::District)->pluck('id');

    expect($multi)->toContain($city->id)
        ->toContain($district->id)
        ->not->toContain($country->id);
});

it('returns null distance for an unlocated place', function () {
    $unlocated = Location::factory()->create(['latitude' => null, 'longitude' => null]);

    expect($unlocated->distanceTo(0.0, 0.0))->toBeNull();
});

it('computes a haversine distance of about 1113 metres for a 0.01 degree shift', function () {
    $located = Location::factory()->create(['latitude' => 0.0, 'longitude' => 0.0]);

    $distance = $located->distanceTo(0.0, 0.01);

    expect($distance)->toBeGreaterThan(1100)->toBeLessThan(1125);
});
