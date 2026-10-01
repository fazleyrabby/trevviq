<?php

namespace App\Services\Location;

use App\Jobs\RecomputeLocationIndexability;
use App\Models\Location;

/**
 * Keeps a location's denormalized `content_count` in step with its approved
 * content, then nudges the indexation gate. Videos and events extend `count()`
 * as those modules land.
 */
class LocationContentService
{
    public function count(Location $location): int
    {
        return $location->reviews()->approved()->count()
            + $location->videos()->published()->count()
            + $location->events()->published()->count();
    }

    /**
     * Recompute `content_count` and queue an indexation refresh.
     */
    public function recompute(Location $location): int
    {
        $count = $this->count($location);

        if ($location->content_count !== $count) {
            $location->forceFill(['content_count' => $count])->saveQuietly();
        }

        RecomputeLocationIndexability::dispatch($location->id);

        return $count;
    }
}
