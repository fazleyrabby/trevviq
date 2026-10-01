<?php

namespace App\Services\Location\Import;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\LocationImportBatch;
use Closure;
use RuntimeException;
use Throwable;

/**
 * Idempotent importer for the GeoNames administrative tree.
 *
 * Rows are classified by feature code into country / region / district / city:
 *
 * - First streaming pass: upsert countries and buffer region rows, then write
 *   the buffered regions once every country is known.
 * - Second streaming pass: upsert districts and cities, resolving each parent
 *   from the country and region maps assembled in the first pass.
 *
 * Both passes read the file line by line so the source is never resident in
 * memory. Identity is the external key (`osm_type = geonames`, `osm_id`),
 * written through `updateOrCreate` so the model's slug/depth hooks always run.
 */
final class GeoNamesImporter
{
    private const SOURCE = 'geonames';

    private const PROGRESS_INTERVAL = 1000;

    private bool $dryRun = false;

    private ?LocationImportBatch $batch = null;

    private ?Closure $onProgress = null;

    private ImportResult $result;

    /**
     * @param  array<int, string>|null  $countries  ISO-3166 alpha-2 allow-list, or null for all.
     * @param  Closure(ImportResult): void|null  $onProgress  Called every 1000 processed rows.
     */
    public function import(
        string $file,
        ?array $countries = null,
        bool $fresh = false,
        bool $dryRun = false,
        ?Closure $onProgress = null,
    ): ImportResult {
        $this->assertReadable($file);

        $wanted = $this->normalizeCountries($countries);

        $this->dryRun = $dryRun;
        $this->onProgress = $onProgress;
        $this->result = new ImportResult;
        $this->batch = $dryRun ? null : $this->startBatch($wanted);

        try {
            if ($fresh && ! $dryRun) {
                $this->deactivateExisting();
            }

            /** @var array<string, int> $countryIds */
            $countryIds = [];

            /** @var array<int, GeoNamesRow> $regionRows */
            $regionRows = [];

            $this->eachRow($file, function (GeoNamesRow $row) use ($wanted, &$countryIds, &$regionRows): void {
                if (! $this->isWanted($row, $wanted)) {
                    return;
                }

                $type = GeoNamesFeatureMapper::map($row->featureClass, $row->featureCode);

                if ($type === LocationType::Country) {
                    $id = $this->persist($row, $type, null);

                    if ($id !== null) {
                        $countryIds[$row->countryCode] = $id;
                    }
                } elseif ($type === LocationType::Region) {
                    $regionRows[] = $row;
                }
            });

            $regionIds = $this->persistRegions($regionRows, $countryIds);

            $this->eachRow($file, function (GeoNamesRow $row) use ($wanted, $countryIds, $regionIds): void {
                if (! $this->isWanted($row, $wanted)) {
                    return;
                }

                $type = GeoNamesFeatureMapper::map($row->featureClass, $row->featureCode);

                if ($type !== LocationType::District && $type !== LocationType::City) {
                    return;
                }

                $parentId = $regionIds[$this->regionKey($row)] ?? $countryIds[$row->countryCode] ?? null;

                $this->persist($row, $type, $parentId);
            });

            $this->completeBatch();
        } catch (Throwable $exception) {
            $this->failBatch($exception);

            throw $exception;
        }

        return $this->result;
    }

    /**
     * Persist buffered region rows, keyed by country code + admin1 for children.
     *
     * @param  array<int, GeoNamesRow>  $rows
     * @param  array<string, int>  $countryIds
     * @return array<string, int>
     */
    private function persistRegions(array $rows, array $countryIds): array
    {
        $regionIds = [];

        foreach ($rows as $row) {
            $parentId = $countryIds[$row->countryCode] ?? null;
            $id = $this->persist($row, LocationType::Region, $parentId);

            if ($id !== null) {
                $regionIds[$this->regionKey($row)] = $id;
            }
        }

        return $regionIds;
    }

    /**
     * Upsert (or, in dry-run, only count) a single classified row.
     */
    private function persist(GeoNamesRow $row, LocationType $type, ?int $parentId): ?int
    {
        if (! $this->isImportable($row, $type)) {
            $this->skipped();

            return null;
        }

        if ($this->dryRun) {
            $exists = Location::query()
                ->where('osm_type', self::SOURCE)
                ->where('osm_id', $row->geonameId)
                ->exists();

            $exists ? $this->updated() : $this->created();

            return null;
        }

        $location = Location::updateOrCreate(
            ['osm_type' => self::SOURCE, 'osm_id' => $row->geonameId],
            $this->attributes($row, $type, $parentId),
        );

        $location->wasRecentlyCreated ? $this->created() : $this->updated();

        return (int) $location->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(GeoNamesRow $row, LocationType $type, ?int $parentId): array
    {
        return [
            'parent_id' => $parentId,
            'type' => $type,
            'name' => $row->name,
            'country_code' => $row->countryCode !== '' ? $row->countryCode : null,
            'region_code' => $row->admin1 !== '' ? $row->admin1 : null,
            'city_name' => $type === LocationType::City ? $row->name : null,
            'latitude' => $row->latitude,
            'longitude' => $row->longitude,
            'population' => $row->population > 0 ? $row->population : null,
            'timezone' => $row->timezone !== '' ? $row->timezone : null,
            'osm_type' => self::SOURCE,
            'osm_id' => $row->geonameId,
            'is_active' => true,
            'metadata' => [
                'population' => $row->population,
                'geoname_feature_code' => $row->featureCode,
                'source' => self::SOURCE,
            ],
        ];
    }

    /**
     * A row can only be written when it has a name and, for non-countries, a
     * country code to anchor it in the tree.
     */
    private function isImportable(GeoNamesRow $row, LocationType $type): bool
    {
        if ($row->name === '') {
            return false;
        }

        return $type === LocationType::Country || $row->countryCode !== '';
    }

    /**
     * @param  array<int, string>|null  $countries
     */
    private function isWanted(GeoNamesRow $row, ?array $countries): bool
    {
        return $countries === null || in_array($row->countryCode, $countries, true);
    }

    private function regionKey(GeoNamesRow $row): string
    {
        return $row->countryCode.'|'.$row->admin1;
    }

    private function created(): void
    {
        $this->result->created++;
        $this->tick();
    }

    private function updated(): void
    {
        $this->result->updated++;
        $this->tick();
    }

    private function skipped(): void
    {
        $this->result->skipped++;
        $this->tick();
    }

    /**
     * Advance the processed counter and periodically flush progress.
     */
    private function tick(): void
    {
        $this->result->processed++;

        if ($this->result->processed % self::PROGRESS_INTERVAL !== 0) {
            return;
        }

        $this->flushBatch();

        if ($this->onProgress !== null) {
            ($this->onProgress)($this->result);
        }
    }

    /**
     * Stream a file line by line, invoking the callback for each parsed row.
     *
     * @param  Closure(GeoNamesRow): void  $callback
     */
    private function eachRow(string $file, Closure $callback): void
    {
        $handle = fopen($file, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open GeoNames file: {$file}");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $row = GeoNamesRow::fromLine($line);

                if ($row !== null) {
                    $callback($row);
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function assertReadable(string $file): void
    {
        if ($file === '' || ! is_file($file) || ! is_readable($file)) {
            throw new RuntimeException("GeoNames file not found or unreadable: {$file}");
        }
    }

    /**
     * @param  array<int, string>|null  $countries
     * @return array<int, string>|null
     */
    private function normalizeCountries(?array $countries): ?array
    {
        if ($countries === null) {
            return null;
        }

        $normalized = array_values(array_unique(array_filter(
            array_map(static fn (string $country): string => strtoupper(trim($country)), $countries),
            static fn (string $country): bool => $country !== '',
        )));

        return $normalized === [] ? null : $normalized;
    }

    private function startBatch(?array $countries): LocationImportBatch
    {
        return LocationImportBatch::create([
            'source' => self::SOURCE,
            'status' => 'running',
            'countries' => $countries,
            'started_at' => now(),
        ]);
    }

    private function flushBatch(): void
    {
        if ($this->batch === null) {
            return;
        }

        $this->batch->fill($this->result->toArray())->save();
    }

    private function completeBatch(): void
    {
        if ($this->batch === null) {
            return;
        }

        $this->batch->fill([
            ...$this->result->toArray(),
            'status' => 'completed',
            'finished_at' => now(),
        ])->save();
    }

    private function failBatch(Throwable $exception): void
    {
        if ($this->batch === null) {
            return;
        }

        $this->batch->fill([
            ...$this->result->toArray(),
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'finished_at' => now(),
        ])->save();
    }

    /**
     * Soft-deactivate every existing GeoNames row before a fresh import; rows
     * present in the new snapshot are re-activated through `updateOrCreate`.
     */
    private function deactivateExisting(): void
    {
        Location::query()
            ->where('osm_type', self::SOURCE)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
