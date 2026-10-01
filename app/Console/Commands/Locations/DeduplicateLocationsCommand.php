<?php

namespace App\Console\Commands\Locations;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DeduplicateLocationsCommand extends Command
{
    protected $signature = 'locations:deduplicate
        {--dry-run : Report intended changes without writing to the database}';

    protected $description = 'Flag and soft-deactivate duplicate locations using the geographic dedup keys';

    private const int CHUNK_SIZE = 500;

    private const float DUPLICATE_METERS = 150.0;

    private const float AMBIGUOUS_METERS = 25.0;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        /** @var array<int, bool> $handled */
        $handled = [];
        $groups = 0;
        $deactivated = 0;

        Location::query()
            ->active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->chunkById(self::CHUNK_SIZE, function (Collection $locations) use (&$handled, &$groups, &$deactivated, $dryRun): void {
                foreach ($locations as $location) {
                    if (isset($handled[$location->getKey()])) {
                        continue;
                    }

                    $group = $this->duplicateGroup($location, $handled);
                    $handled[$location->getKey()] = true;

                    if ($group->count() < 2) {
                        continue;
                    }

                    $group->each(fn (Location $member) => $handled[$member->getKey()] = true);

                    $groups++;
                    $deactivated += $group->count() - 1;

                    if ($dryRun) {
                        continue;
                    }

                    $group
                        ->sortBy('id')
                        ->skip(1)
                        ->each(fn (Location $duplicate) => $this->flagAndDeactivate($duplicate));
                }
            });

        $flagged = $this->flagAmbiguousNeighbours($dryRun);

        $this->info(sprintf(
            '%s %d duplicate rows across %d groups (lowest id kept).',
            $dryRun ? 'Would deactivate' : 'Deactivated',
            $deactivated,
            $groups,
        ));
        $this->info(sprintf(
            '%s %d rows with a different name within %dm.',
            $dryRun ? 'Would flag' : 'Flagged',
            $flagged,
            (int) self::AMBIGUOUS_METERS,
        ));

        return self::SUCCESS;
    }

    /**
     * Same type + normalized name + within 150 m of the anchor.
     *
     * @param  array<int, bool>  $handled
     * @return Collection<int, Location>
     */
    private function duplicateGroup(Location $anchor, array $handled): Collection
    {
        $normalizedName = $this->normalizeName((string) $anchor->name);

        return Location::query()
            ->ofType($anchor->type)
            ->active()
            ->whereKeyNot($anchor->getKey())
            ->withinBoundingBox((float) $anchor->latitude, (float) $anchor->longitude, (int) self::DUPLICATE_METERS)
            ->get()
            ->filter(function (Location $candidate) use ($anchor, $handled, $normalizedName): bool {
                if (isset($handled[$candidate->getKey()])) {
                    return false;
                }

                if ($this->normalizeName((string) $candidate->name) !== $normalizedName) {
                    return false;
                }

                $distance = $anchor->distanceTo((float) $candidate->latitude, (float) $candidate->longitude);

                return $distance !== null && $distance <= self::DUPLICATE_METERS;
            })
            ->prepend($anchor)
            ->values();
    }

    /**
     * Flag, without deactivating, rows of the same type but a different name
     * sitting within 25 m of another active location.
     */
    private function flagAmbiguousNeighbours(bool $dryRun): int
    {
        $flagged = 0;

        Location::query()
            ->active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->chunkById(self::CHUNK_SIZE, function (Collection $locations) use (&$flagged, $dryRun): void {
                foreach ($locations as $location) {
                    if (! $this->hasAmbiguousNeighbour($location)) {
                        continue;
                    }

                    if ((bool) ($location->metadata['dedup_candidate'] ?? false) === false) {
                        $flagged++;
                    }

                    if (! $dryRun) {
                        $this->flag($location);
                    }
                }
            });

        return $flagged;
    }

    private function hasAmbiguousNeighbour(Location $anchor): bool
    {
        $normalizedName = $this->normalizeName((string) $anchor->name);

        return Location::query()
            ->ofType($anchor->type)
            ->active()
            ->whereKeyNot($anchor->getKey())
            ->withinBoundingBox((float) $anchor->latitude, (float) $anchor->longitude, (int) self::AMBIGUOUS_METERS)
            ->get()
            ->contains(function (Location $candidate) use ($anchor, $normalizedName): bool {
                if ($this->normalizeName((string) $candidate->name) === $normalizedName) {
                    return false;
                }

                $distance = $anchor->distanceTo((float) $candidate->latitude, (float) $candidate->longitude);

                return $distance !== null && $distance <= self::AMBIGUOUS_METERS;
            });
    }

    private function flagAndDeactivate(Location $duplicate): void
    {
        $metadata = $duplicate->metadata ?? [];
        $metadata['dedup_candidate'] = true;

        $duplicate->metadata = $metadata;
        $duplicate->is_active = false;
        $duplicate->saveQuietly();
    }

    private function flag(Location $location): void
    {
        $metadata = $location->metadata ?? [];

        if (($metadata['dedup_candidate'] ?? false) === true) {
            return;
        }

        $metadata['dedup_candidate'] = true;

        $location->metadata = $metadata;
        $location->saveQuietly();
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower($this->collapseWhitespace($name));
    }

    private function collapseWhitespace(string $value): string
    {
        return trim((string) preg_replace('/[\s\p{Z}]+/u', ' ', $value));
    }
}
