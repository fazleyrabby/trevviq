<?php

namespace App\Services\Location;

use App\Enums\LocationType;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Support\Collection;

/**
 * Deterministic discovery surfaces (Section 25). Until traveller content
 * exists, popularity falls back to population and recency, so the homepage is
 * never empty and never fabricated.
 */
class DiscoveryService
{
    /**
     * @return Collection<int, Location>
     */
    public function popularCountries(int $limit = 12): Collection
    {
        return Location::query()
            ->active()
            ->ofType(LocationType::Country)
            ->orderByDesc('population')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Location>
     */
    public function popularCities(int $limit = 12): Collection
    {
        return Location::query()
            ->active()
            ->ofType(LocationType::City)
            ->orderByDesc('content_count')
            ->orderByDesc('population')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Location>
     */
    public function trendingDestinations(int $limit = 8): Collection
    {
        return Location::query()
            ->active()
            ->ofType(LocationType::Country, LocationType::City, LocationType::Region)
            ->orderByDesc('content_count')
            ->orderByDesc('population')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Location>
     */
    public function recentlyAdded(int $limit = 12): Collection
    {
        return Location::query()
            ->active()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Points of interest with no traveller content yet — the places still to be
     * discovered.
     *
     * @return Collection<int, Location>
     */
    public function hiddenGems(int $limit = 12): Collection
    {
        return Location::query()
            ->active()
            ->ofType(...LocationType::places())
            ->where('content_count', 0)
            ->orderByDesc('population')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Event>
     */
    public function upcomingEvents(int $limit = 6): Collection
    {
        return Event::query()
            ->published()
            ->upcoming()
            ->with('location')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }
}
