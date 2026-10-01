<?php

use App\Jobs\RecomputeLocationIndexability;
use App\Models\Location;
use Illuminate\Support\Facades\Queue;

it('marks a verified location as indexable', function () {
    $location = Location::factory()->create();

    $location->update(['is_verified' => true]);

    expect($location->fresh()->indexable)->toBeTrue();
});

it('recomputes indexability from the create path too', function () {
    $location = Location::factory()->create(['is_verified' => true]);

    expect($location->fresh()->indexable)->toBeTrue();
});

it('does not mark a plain location as indexable', function () {
    $location = Location::factory()->create();

    expect($location->fresh()->indexable)->toBeFalse();
});

it('applies the content threshold when deciding indexability', function (int $contentCount, bool $indexable) {
    $location = Location::factory()->create();

    $location->update(['content_count' => $contentCount]);

    expect($location->fresh()->indexable)->toBe($indexable);
})->with([
    'at the threshold' => [3, true],
    'below the threshold' => [2, false],
]);

it('applies the description length rule when deciding indexability', function (int $length, bool $indexable) {
    $location = Location::factory()->create();

    $location->update(['description' => str_repeat('a', $length)]);

    expect($location->fresh()->indexable)->toBe($indexable);
})->with([
    'at the minimum length' => [120, true],
    'below the minimum length' => [119, false],
]);

it('makes the ancestors of an indexable location indexable', function () {
    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();

    $city->update(['is_verified' => true]);

    expect($city->fresh()->indexable)->toBeTrue()
        ->and($country->fresh()->indexable)->toBeTrue();
});

it('propagates indexability up the ancestor chain when the job runs', function () {
    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();
    $district = Location::factory()->district()->childOf($city)->create();

    $district->update(['is_verified' => true]);

    expect($district->fresh()->indexable)->toBeTrue()
        ->and($city->fresh()->indexable)->toBeTrue()
        ->and($country->fresh()->indexable)->toBeTrue();
});

it('rebuilds the flags for the whole tree with the reindex command', function () {
    Queue::fake();

    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();

    $city->update(['is_verified' => true]);

    Queue::assertPushed(RecomputeLocationIndexability::class);

    expect($city->fresh()->indexable)->toBeFalse()
        ->and($country->fresh()->indexable)->toBeFalse();

    $this->artisan('locations:reindex')->assertExitCode(0);

    expect($city->fresh()->indexable)->toBeTrue()
        ->and($country->fresh()->indexable)->toBeTrue();
});

it('writes nothing on a reindex dry run', function () {
    Queue::fake();

    $country = Location::factory()->country()->create();
    $city = Location::factory()->city()->childOf($country)->create();

    $city->update(['is_verified' => true]);

    $this->artisan('locations:reindex', ['--dry-run' => true])->assertExitCode(0);

    expect($city->fresh()->indexable)->toBeFalse()
        ->and($country->fresh()->indexable)->toBeFalse();
});
