<?php

namespace App\Console\Commands\Locations;

use App\Models\Location;
use App\Services\Location\LocationSlugService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RebuildSlugsCommand extends Command
{
    protected $signature = 'locations:rebuild-slugs
        {--dry-run : Report intended changes without writing to the database}';

    protected $description = 'Rebuild location slugs and full slugs in hierarchy order';

    private const int CHUNK_SIZE = 500;

    public function __construct(private readonly LocationSlugService $slugs)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $scanned = 0;
        $changed = 0;
        $slugsFilled = 0;

        /** @var array<int, string> $parentOverrides Full slugs of the previous depth level. */
        $parentOverrides = [];

        $depths = Location::query()
            ->distinct()
            ->orderBy('depth')
            ->pluck('depth');

        foreach ($depths as $depth) {
            /** @var array<int, string> $levelOverrides */
            $levelOverrides = [];

            Location::query()
                ->with('parent')
                ->where('depth', $depth)
                ->chunkById(self::CHUNK_SIZE, function (Collection $locations) use (
                    &$scanned,
                    &$changed,
                    &$slugsFilled,
                    &$levelOverrides,
                    $parentOverrides,
                    $dryRun,
                ): void {
                    foreach ($locations as $location) {
                        $scanned++;

                        if (blank($location->slug)) {
                            $location->slug = $this->slugs->slugFor((string) $location->name);
                            $slugsFilled++;
                        }

                        $this->injectParentOverride($location, $parentOverrides);

                        $candidate = $this->slugs->uniqueFullSlug($location);
                        $original = (string) $location->getOriginal('full_slug');

                        if ($candidate === $original && ! $location->isDirty('slug')) {
                            continue;
                        }

                        $levelOverrides[$location->getKey()] = $candidate;

                        if ($candidate !== $original) {
                            $changed++;
                        }

                        if ($dryRun) {
                            continue;
                        }

                        $location->full_slug = $candidate;
                        $location->save();
                    }
                });

            $parentOverrides = $levelOverrides;
        }

        $this->info(sprintf(
            '%s %d of %d full slugs (%d blank slugs filled).',
            $dryRun ? 'Would change' : 'Changed',
            $changed,
            $scanned,
            $slugsFilled,
        ));

        return self::SUCCESS;
    }

    /**
     * Force the parent relation to expose the intended full slug from the depth
     * level currently being processed so nested paths are rebuilt consistently
     * even while running with --dry-run.
     *
     * @param  array<int, string>  $overrides
     */
    private function injectParentOverride(Location $location, array $overrides): void
    {
        if ($location->parent_id === null || ! array_key_exists($location->parent_id, $overrides)) {
            return;
        }

        $parent = $location->parent ?? new Location;
        $parent->setAttribute('id', $location->parent_id);
        $parent->setAttribute('full_slug', $overrides[$location->parent_id]);

        $location->setRelation('parent', $parent);
    }
}
