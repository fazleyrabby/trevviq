<?php

use App\Enums\LocationType;
use App\Models\Location;
use App\Services\Location\DiscoveryService;

it('returns only countries ordered by population descending', function () {
    $lowPopulation = Location::factory()->country()->create([
        'name' => 'Lowland',
        'population' => 1_000,
    ]);
    $highPopulation = Location::factory()->country()->create([
        'name' => 'Highland',
        'population' => 9_000_000,
    ]);
    $city = Location::factory()->city()->create([
        'name' => 'Megacity',
        'population' => 50_000_000,
    ]);

    $results = app(DiscoveryService::class)->popularCountries();

    expect($results->pluck('id')->all())->toBe([$highPopulation->id, $lowPopulation->id])
        ->and($results->pluck('type')->unique()->all())->toBe([LocationType::Country])
        ->and($results->pluck('id'))->not->toContain($city->id);
});

it('returns only cities ordered by content count then population descending', function () {
    $fewContentSmall = Location::factory()->city()->create([
        'name' => 'Fewville',
        'content_count' => 5,
        'population' => 100,
    ]);
    $fewContentLarge = Location::factory()->city()->create([
        'name' => 'Fewton',
        'content_count' => 5,
        'population' => 800,
    ]);
    $mostContent = Location::factory()->city()->create([
        'name' => 'Busytown',
        'content_count' => 20,
        'population' => 10,
    ]);
    $country = Location::factory()->country()->create([
        'name' => 'Notacity',
        'content_count' => 999,
        'population' => 999_999,
    ]);

    $results = app(DiscoveryService::class)->popularCities();

    expect($results->pluck('id')->all())->toBe([
        $mostContent->id,
        $fewContentLarge->id,
        $fewContentSmall->id,
    ])
        ->and($results->pluck('type')->unique()->all())->toBe([LocationType::City])
        ->and($results->pluck('id'))->not->toContain($country->id);
});

it('includes countries cities and regions but excludes points of interest ordered by content count descending', function () {
    $country = Location::factory()->country()->create(['name' => 'Trend Country', 'content_count' => 3]);
    $city = Location::factory()->city()->create(['name' => 'Trend City', 'content_count' => 7]);
    $region = Location::factory()->type(LocationType::Region)->create(['name' => 'Trend Region', 'content_count' => 5]);
    $attraction = Location::factory()->type(LocationType::Attraction)->create([
        'name' => 'Trend Attraction',
        'content_count' => 100,
    ]);

    $results = app(DiscoveryService::class)->trendingDestinations();

    expect($results->pluck('id')->all())->toBe([$city->id, $region->id, $country->id])
        ->and($results->pluck('id'))->not->toContain($attraction->id);
});

it('returns the newest locations first', function () {
    $oldest = Location::factory()->create([
        'name' => 'Oldest Place',
        'created_at' => now()->subDays(3),
    ]);
    $middle = Location::factory()->create([
        'name' => 'Middle Place',
        'created_at' => now()->subDays(2),
    ]);
    $newest = Location::factory()->create([
        'name' => 'Newest Place',
        'created_at' => now()->subDay(),
    ]);

    $results = app(DiscoveryService::class)->recentlyAdded();

    expect($results->pluck('id')->all())->toBe([$newest->id, $middle->id, $oldest->id])
        ->and($results->first()->is($newest))->toBeTrue();
});

it('returns only points of interest with no content and excludes administrative rows', function () {
    $attraction = Location::factory()->type(LocationType::Attraction)->create([
        'name' => 'Undiscovered Attraction',
        'content_count' => 0,
    ]);
    $beach = Location::factory()->type(LocationType::Beach)->create([
        'name' => 'Undiscovered Beach',
        'content_count' => 0,
    ]);
    $withContent = Location::factory()->type(LocationType::Museum)->create([
        'name' => 'Popular Museum',
        'content_count' => 12,
    ]);
    $emptyCity = Location::factory()->city()->create([
        'name' => 'Empty City',
        'content_count' => 0,
    ]);
    $emptyCountry = Location::factory()->country()->create([
        'name' => 'Empty Country',
        'content_count' => 0,
    ]);

    $results = app(DiscoveryService::class)->hiddenGems();

    expect($results->pluck('id'))->toContain($attraction->id)
        ->toContain($beach->id)
        ->not->toContain($withContent->id)
        ->not->toContain($emptyCity->id)
        ->not->toContain($emptyCountry->id)
        ->and($results->every(fn (Location $location): bool => $location->type->isPlace()))->toBeTrue()
        ->and($results->every(fn (Location $location): bool => $location->content_count === 0))->toBeTrue();
});
