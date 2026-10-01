<?php

namespace App\Console\Commands\Locations;

use App\Models\Location;
use App\Services\Location\LocationSlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NormalizeLocationsCommand extends Command
{
    protected $signature = 'locations:normalize
        {--dry-run : Report intended changes without writing to the database}';

    protected $description = 'Normalize location names, codes, depth, search index and missing slugs';

    private const int CHUNK_SIZE = 500;

    public function __construct(private readonly LocationSlugService $slugs)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        /** @var array<string, int> $counts */
        $counts = [
            'scanned' => 0,
            'names' => 0,
            'slugs' => 0,
            'country_codes' => 0,
            'nulled' => 0,
            'depths' => 0,
            'search_indexes' => 0,
            'full_slugs' => 0,
            'changed' => 0,
        ];

        /** @var array<int, array{depth:int, search_index:?string, full_slug:string}> $parentOverrides */
        $parentOverrides = [];

        $depths = Location::query()
            ->distinct()
            ->orderBy('depth')
            ->pluck('depth');

        foreach ($depths as $depth) {
            /** @var array<int, array{depth:int, search_index:?string, full_slug:string}> $levelOverrides */
            $levelOverrides = [];

            Location::query()
                ->with('parent')
                ->where('depth', $depth)
                ->chunkById(self::CHUNK_SIZE, function (Collection $locations) use (
                    &$counts,
                    &$levelOverrides,
                    $parentOverrides,
                    $dryRun,
                ): void {
                    foreach ($locations as $location) {
                        $counts['scanned']++;

                        $relation = $this->resolveParent($location, $parentOverrides);
                        $parent = $relation['parent'];
                        $state = $relation['state'];

                        $this->normalizeName($location, $counts);
                        $this->normalizeSlug($location, $counts);
                        $this->normalizeCountryCode($location, $counts);
                        $this->nullEmptyStrings($location, $counts);

                        $depth = ($parent !== null || $state !== null)
                            ? ($state['depth'] ?? (int) $parent->depth) + 1
                            : 0;

                        if ((int) $location->depth !== $depth) {
                            $location->depth = $depth;
                            $counts['depths']++;
                        }

                        $parentIndex = $state['search_index'] ?? $parent?->search_index;
                        $searchIndex = trim(implode(' ', array_filter([$parentIndex, $location->name])));

                        if ($location->search_index !== $searchIndex) {
                            $location->search_index = $searchIndex;
                            $counts['search_indexes']++;
                        }

                        if (blank($location->full_slug)) {
                            $location->full_slug = $this->slugs->uniqueFullSlug($location);
                            $counts['full_slugs']++;
                        }

                        if (! $location->isDirty()) {
                            continue;
                        }

                        $counts['changed']++;

                        $levelOverrides[$location->getKey()] = [
                            'depth' => (int) $location->depth,
                            'search_index' => $location->search_index,
                            'full_slug' => (string) $location->full_slug,
                        ];

                        if (! $dryRun) {
                            $location->saveQuietly();
                        }
                    }
                });

            $parentOverrides = $levelOverrides;
        }

        $this->report($counts, $dryRun);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function normalizeName(Location $location, array &$counts): void
    {
        $normalized = $this->collapseWhitespace((string) $location->name);

        if ($normalized !== $location->name) {
            $location->name = $normalized;
            $counts['names']++;
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function normalizeSlug(Location $location, array &$counts): void
    {
        if (blank($location->slug)) {
            $location->slug = $this->slugs->slugFor((string) $location->name);
            $counts['slugs']++;
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function normalizeCountryCode(Location $location, array &$counts): void
    {
        $code = $location->country_code;

        if ($code === null) {
            return;
        }

        $trimmed = trim((string) $code);
        $normalized = strlen($trimmed) === 2 ? strtoupper($trimmed) : $trimmed;

        if ($normalized !== $code) {
            $location->country_code = $normalized;
            $counts['country_codes']++;
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function nullEmptyStrings(Location $location, array &$counts): void
    {
        foreach (['region_code', 'city_name', 'timezone'] as $field) {
            if ($location->{$field} === '') {
                $location->{$field} = null;
                $counts['nulled']++;
            }
        }
    }

    /**
     * Resolve the parent with the intended values from the previous depth level
     * so children inherit freshly normalized depth, search index and full slug.
     *
     * @param  array<int, array{depth:int, search_index:?string, full_slug:string}>  $overrides
     * @return array{parent: ?Location, state: ?array{depth:int, search_index:?string, full_slug:string}}
     */
    private function resolveParent(Location $location, array $overrides): array
    {
        if ($location->parent_id === null) {
            return ['parent' => null, 'state' => null];
        }

        $state = $overrides[$location->parent_id] ?? null;
        $parent = $location->parent;

        if ($parent !== null && $state !== null) {
            $parent->setAttribute('depth', $state['depth']);
            $parent->setAttribute('search_index', $state['search_index']);
            $parent->setAttribute('full_slug', $state['full_slug']);
        }

        return ['parent' => $parent, 'state' => $state];
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function report(array $counts, bool $dryRun): void
    {
        $prefix = $dryRun ? 'Would update' : 'Updated';

        $this->info(sprintf('%s %d of %d locations.', $prefix, $counts['changed'], $counts['scanned']));
        $this->line(sprintf('  names: %d', $counts['names']));
        $this->line(sprintf('  slugs filled: %d', $counts['slugs']));
        $this->line(sprintf('  country codes: %d', $counts['country_codes']));
        $this->line(sprintf('  empty strings nulled: %d', $counts['nulled']));
        $this->line(sprintf('  depths: %d', $counts['depths']));
        $this->line(sprintf('  search indexes: %d', $counts['search_indexes']));
        $this->line(sprintf('  full slugs filled: %d', $counts['full_slugs']));
    }

    private function collapseWhitespace(string $value): string
    {
        return trim((string) preg_replace('/[\s\p{Z}]+/u', ' ', $value));
    }
}
