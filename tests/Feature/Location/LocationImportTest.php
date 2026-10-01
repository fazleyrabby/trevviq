<?php

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\LocationImportBatch;

if (! function_exists('trevviqGeoNamesRows')) {
    /**
     * @return array<int, array<int, string>>
     */
    function trevviqGeoNamesRows(): array
    {
        return [
            ['1001', 'Bangladesh', 'Bangladesh', '', '23.6850', '90.3563', 'P', 'PCLI', 'BD', '', '', '', '', '', '170000000', '', '', 'Asia/Dhaka', '2024-01-01'],
            ['1002', 'Dhaka', 'Dhaka', '', '23.8103', '90.4125', 'A', 'ADM1', 'BD', '', '81', '', '', '', '10000000', '', '', 'Asia/Dhaka', '2024-01-01'],
            ['1003', 'Dhaka District', 'Dhaka District', '', '23.7000', '90.4000', 'A', 'ADM2', 'BD', '', '81', '26', '', '', '5000000', '', '', 'Asia/Dhaka', '2024-01-01'],
            ['1004', 'Dhaka', 'Dhaka', '', '23.8103', '90.4125', 'P', 'PPLA', 'BD', '', '81', '', '', '', '8900000', '', '', 'Asia/Dhaka', '2024-01-01'],
            ['1005', 'Patenga', 'Patenga', '', '22.2350', '91.7900', 'P', 'PPL', 'BD', '', '81', '', '', '', '100000', '', '', 'Asia/Dhaka', '2024-01-01'],
            ['2001', 'Japan', 'Japan', '', '36.2048', '138.2529', 'P', 'PCLI', 'JP', '', '', '', '', '', '125000000', '', '', 'Asia/Tokyo', '2024-01-01'],
            ['3001', 'Some Stream', 'Some Stream', '', '10.0000', '20.0000', 'H', 'STM', 'BD', '', '81', '', '', '', '0', '', '', 'Asia/Dhaka', '2024-01-01'],
        ];
    }
}

if (! function_exists('trevviqWriteGeoNamesFixture')) {
    /**
     * @param  array<int, array<int, string>>  $rows
     */
    function trevviqWriteGeoNamesFixture(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'trevviq_geonames_');
        $handle = fopen($path, 'wb');

        foreach ($rows as $row) {
            fputcsv($handle, $row, "\t", '"', '\\');
        }

        fclose($handle);

        register_shutdown_function(static function () use ($path): void {
            if (is_file($path)) {
                @unlink($path);
            }
        });

        return $path;
    }
}

it('imports a country hierarchy and excludes filtered countries', function () {
    $path = trevviqWriteGeoNamesFixture(trevviqGeoNamesRows());

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD'])->assertExitCode(0);

    $country = Location::query()->where('osm_type', 'geonames')->where('osm_id', '1001')->first();
    expect($country)->not->toBeNull()
        ->and($country->type)->toBe(LocationType::Country)
        ->and($country->name)->toBe('Bangladesh');

    $region = Location::query()->where('osm_type', 'geonames')->where('osm_id', '1002')->first();
    expect($region)->not->toBeNull()
        ->and($region->type)->toBe(LocationType::Region)
        ->and($region->parent_id)->toBe($country->id);

    $city = Location::query()->where('osm_type', 'geonames')->where('osm_id', '1004')->first();
    expect($city)->not->toBeNull()
        ->and($city->type)->toBe(LocationType::City)
        ->and($city->parent_id)->toBe($region->id);

    $this->assertDatabaseMissing('locations', ['osm_type' => 'geonames', 'osm_id' => '2001']);
    $this->assertDatabaseMissing('locations', ['osm_type' => 'geonames', 'osm_id' => '3001']);
});

it('is idempotent across repeated runs', function () {
    $path = trevviqWriteGeoNamesFixture(trevviqGeoNamesRows());

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD'])->assertExitCode(0);
    $count = Location::count();

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD'])->assertExitCode(0);

    expect(Location::count())->toBe($count);

    $duplicateSlugs = Location::query()
        ->selectRaw('full_slug, COUNT(*) as aggregate')
        ->groupBy('full_slug')
        ->havingRaw('COUNT(*) > 1')
        ->get()
        ->count();

    expect($duplicateSlugs)->toBe(0);
});

it('writes nothing on a dry run', function () {
    $path = trevviqWriteGeoNamesFixture(trevviqGeoNamesRows());

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD', '--dry-run' => true])
        ->assertExitCode(0);

    expect(Location::count())->toBe(0)
        ->and(LocationImportBatch::count())->toBe(0);
});

it('records a completed import batch with counters', function () {
    $path = trevviqWriteGeoNamesFixture(trevviqGeoNamesRows());

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD'])->assertExitCode(0);

    $batch = LocationImportBatch::query()->latest('id')->first();

    expect($batch)->not->toBeNull()
        ->and($batch->source)->toBe('geonames')
        ->and($batch->status)->toBe('completed')
        ->and($batch->created)->toBeGreaterThan(0)
        ->and($batch->processed)->toBeGreaterThan(0);
});

it('soft-deactivates stale geonames rows on a fresh import', function () {
    $stale = Location::factory()->create([
        'name' => 'Stale Place',
        'osm_type' => 'geonames',
        'osm_id' => '9999',
        'is_active' => true,
    ]);

    $path = trevviqWriteGeoNamesFixture(trevviqGeoNamesRows());

    $this->artisan('locations:import', ['--file' => $path, '--country' => 'BD', '--fresh' => true])
        ->assertExitCode(0);

    expect($stale->fresh()->is_active)->toBeFalse();
    $this->assertModelExists($stale);
});
