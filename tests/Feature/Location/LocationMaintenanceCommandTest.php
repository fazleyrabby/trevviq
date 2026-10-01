<?php

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

it('rebuilds a corrupted full slug from the parent chain', function () {
    $country = Location::factory()->country()->create(['name' => 'Bangladesh']);
    $city = Location::factory()->city()->childOf($country)->create(['name' => 'Chattogram']);

    DB::table('locations')->where('id', $city->id)->update(['full_slug' => 'broken/legacy-value']);

    $this->artisan('locations:rebuild-slugs')->assertExitCode(0);

    expect($city->fresh()->full_slug)->toBe($country->full_slug.'/'.$city->slug);
});

it('does not change anything on a rebuild-slugs dry run', function () {
    $location = Location::factory()->create(['name' => 'Somewhere']);

    DB::table('locations')->where('id', $location->id)->update(['full_slug' => 'broken-legacy']);

    $this->artisan('locations:rebuild-slugs', ['--dry-run' => true])->assertExitCode(0);

    expect($location->fresh()->full_slug)->toBe('broken-legacy');
});

it('normalizes whitespace, country code, and search index', function () {
    $country = Location::factory()->country()->create(['name' => 'France']);

    $id = DB::table('locations')->insertGetId([
        'parent_id' => $country->id,
        'type' => LocationType::City->value,
        'name' => '  Paris  ',
        'slug' => '',
        'country_code' => 'fr',
        'full_slug' => 'fr/paris-legacy',
        'depth' => 1,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('locations:normalize')->assertExitCode(0);

    $city = Location::find($id);

    expect($city->name)->toBe('Paris')
        ->and($city->country_code)->toBe('FR')
        ->and($city->search_index)->toContain('France')
        ->and($city->search_index)->toContain('Paris');
});

it('deactivates duplicate points of interest but keeps the canonical row', function () {
    $canonical = Location::factory()->type(LocationType::Attraction)->create([
        'name' => 'Old Fort',
        'latitude' => 23.0,
        'longitude' => 90.0,
        'is_active' => true,
    ]);

    $duplicate = Location::factory()->type(LocationType::Attraction)->create([
        'name' => 'Old Fort',
        'latitude' => 23.0005,
        'longitude' => 90.0,
        'is_active' => true,
    ]);

    $this->artisan('locations:deduplicate', ['--dry-run' => true])->assertExitCode(0);

    expect($duplicate->fresh()->is_active)->toBeTrue();

    $this->artisan('locations:deduplicate')->assertExitCode(0);

    expect($canonical->fresh()->is_active)->toBeTrue()
        ->and($duplicate->fresh()->is_active)->toBeFalse()
        ->and($duplicate->fresh()->metadata['dedup_candidate'] ?? false)->toBeTrue();
});
