<?php

use App\Models\Location;

const DHAKA_LAT = 23.8103;
const DHAKA_LNG = 90.4125;

/**
 * Latitude offset (in degrees) approximating the given northward distance.
 */
function latOffset(int $meters): float
{
    return $meters / 111320;
}

it('returns only located places inside the radius, nearest first, with a distance', function () {
    $center = Location::factory()->create([
        'name' => 'Dhaka',
        'latitude' => DHAKA_LAT,
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    $near = Location::factory()->create([
        'name' => 'Near Place',
        'latitude' => DHAKA_LAT + latOffset(1000),
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    $far = Location::factory()->create([
        'name' => 'Far Place',
        'latitude' => DHAKA_LAT + latOffset(200000),
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    $results = Location::nearby(DHAKA_LAT, DHAKA_LNG, 5000);

    expect($results->pluck('id'))->toContain($near->id)
        ->not->toContain($far->id)
        ->and($results->first()->id)->toBe($center->id)
        ->and($results->every(fn (Location $location): bool => $location->distance_meters !== null))->toBeTrue()
        ->and($results->every(fn (Location $location): bool => $location->distance_meters <= 5000))->toBeTrue();

    $distances = $results->pluck('distance_meters')->all();
    expect($distances)->toBe(collect($distances)->sort()->values()->all());

    $nearResult = $results->firstWhere('id', $near->id);
    expect($nearResult->distance_meters)->toBeGreaterThan(900)->toBeLessThan(1100);
});

it('excludes locations listed in excludeIds', function () {
    $center = Location::factory()->create([
        'latitude' => DHAKA_LAT,
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    $near = Location::factory()->create([
        'latitude' => DHAKA_LAT + latOffset(1000),
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    $results = Location::nearby(DHAKA_LAT, DHAKA_LNG, 5000, null, [$near->id]);

    expect($results->pluck('id'))->not->toContain($near->id)
        ->toContain($center->id);
});

it('caps the number of results with the limit', function () {
    Location::factory()->create([
        'latitude' => DHAKA_LAT,
        'longitude' => DHAKA_LNG,
        'is_active' => true,
    ]);

    foreach ([1000, 2000, 3000, 4000] as $meters) {
        Location::factory()->create([
            'latitude' => DHAKA_LAT + latOffset($meters),
            'longitude' => DHAKA_LNG,
            'is_active' => true,
        ]);
    }

    $results = Location::nearby(DHAKA_LAT, DHAKA_LNG, 10000, 2);

    expect($results)->toHaveCount(2);
});

it('includes points inside the bounding box and excludes points outside', function () {
    $inside = Location::factory()->create(['latitude' => 0.0, 'longitude' => 0.0]);
    $outside = Location::factory()->create(['latitude' => 0.0, 'longitude' => 0.05]);

    $ids = Location::query()->withinBoundingBox(0.0, 0.0, 1000)->pluck('id');

    expect($ids)->toContain($inside->id)->not->toContain($outside->id);
});
