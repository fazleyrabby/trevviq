<?php

namespace App\Services\Location;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

class LocationIndexationService
{
    /**
     * Whether a location qualifies on its own merit (Section 79): verified,
     * enough approved content, or a substantial editorial description. Top /
     * curated hubs are marked verified by operators and therefore covered here.
     */
    public function indexableBySelf(Location $location): bool
    {
        $threshold = (int) config('trevviq.indexation.content_threshold');
        $minLength = (int) config('trevviq.indexation.description_min_length');

        return $location->is_verified
            || $location->content_count >= $threshold
            || mb_strlen((string) $location->description) >= $minLength;
    }

    /**
     * Recompute a single location and propagate indexability up its ancestor
     * chain. Ancestors are never un-indexed here; run `locations:reindex` for
     * an authoritative pass.
     */
    public function recompute(Location $location, bool $dryRun = false): bool
    {
        $changed = false;
        $self = $this->indexableBySelf($location);

        if ($location->indexable !== $self) {
            $changed = true;

            if (! $dryRun) {
                $location->forceFill([
                    'indexable' => $self,
                    'indexable_updated_at' => now(),
                ])->saveQuietly();
            }
        }

        if (! $self) {
            return $changed;
        }

        foreach ($location->ancestors() as $ancestor) {
            if ($ancestor->indexable) {
                continue;
            }

            $changed = true;

            if (! $dryRun) {
                $ancestor->forceFill([
                    'indexable' => true,
                    'indexable_updated_at' => now(),
                ])->saveQuietly();
            }
        }

        return $changed;
    }

    /**
     * Authoritative rebuild of the whole gate.
     *
     * Pass 1 applies the self rules. Pass 2 propagates indexability to ancestors
     * until stable (bounded by tree depth). Returns the number of locations that
     * changed (or, in dry-run, the number that would change).
     */
    public function recomputeAll(bool $dryRun = false): int
    {
        if ($dryRun) {
            return $this->countWouldChange();
        }

        $changed = Location::query()
            ->where('indexable', false)
            ->where(fn ($query) => $this->selfRule($query))
            ->update([
                'indexable' => true,
                'indexable_updated_at' => now(),
            ]);

        do {
            $parentIds = Location::query()
                ->where('indexable', true)
                ->whereNotNull('parent_id')
                ->distinct()
                ->pluck('parent_id');

            $propagated = Location::query()
                ->whereIn('id', $parentIds)
                ->where('indexable', false)
                ->update([
                    'indexable' => true,
                    'indexable_updated_at' => now(),
                ]);

            $changed += $propagated;
        } while ($propagated > 0);

        return $changed;
    }

    /**
     * @param  Builder<Location>  $query
     * @return Builder<Location>
     */
    private function selfRule($query)
    {
        $threshold = (int) config('trevviq.indexation.content_threshold');
        $minLength = (int) config('trevviq.indexation.description_min_length');

        return $query
            ->where('is_verified', true)
            ->orWhere('content_count', '>=', $threshold)
            ->orWhereRaw('LENGTH(COALESCE(description, \'\')) >= ?', [$minLength]);
    }

    private function countWouldChange(): int
    {
        $indexable = Location::query()
            ->where('indexable', true)
            ->pluck('id')
            ->flip()
            ->all();

        $before = count($indexable);

        $parents = Location::query()
            ->whereNotNull('parent_id')
            ->pluck('parent_id', 'id')
            ->all();

        $queue = Location::query()
            ->where('indexable', false)
            ->where(fn ($query) => $this->selfRule($query))
            ->pluck('id')
            ->all();

        foreach ($queue as $id) {
            $indexable[$id] = true;
        }

        while ($queue !== []) {
            $next = [];

            foreach ($queue as $id) {
                $parentId = $parents[$id] ?? null;

                if ($parentId !== null && ! isset($indexable[$parentId])) {
                    $indexable[$parentId] = true;
                    $next[] = $parentId;
                }
            }

            $queue = $next;
        }

        return count($indexable) - $before;
    }
}
