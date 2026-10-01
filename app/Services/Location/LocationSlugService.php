<?php

namespace App\Services\Location;

use App\Models\Location;
use Illuminate\Support\Str;

class LocationSlugService
{
    /**
     * URL-safe ASCII slug for a name.
     */
    public function slugFor(string $name): string
    {
        return Str::slug($name) ?: 'location';
    }

    /**
     * Reserved first-segments that must not be used as a root location slug.
     *
     * @return array<int, string>
     */
    public function reservedSegments(): array
    {
        return (array) config('trevviq.reserved_slugs', []);
    }

    /**
     * Resolve the stored local slug, disambiguating reserved first-segments for
     * root locations so a country can never shadow an application route.
     */
    public function resolveSlug(Location $location): string
    {
        $slug = $location->slug ?: $this->slugFor((string) $location->name);

        if ($location->parent === null && in_array($slug, $this->reservedSegments(), true)) {
            $slug = $slug.'-place';
        }

        return $slug;
    }

    /**
     * Resolve a globally unique full slug for the given location, appending a
     * deterministic numeric suffix when a sibling (or any location) already
     * owns the path.
     */
    public function uniqueFullSlug(Location $location): string
    {
        $parent = $location->parent;
        $slug = $this->resolveSlug($location);

        $base = $parent?->full_slug ? $parent->full_slug.'/'.$slug : $slug;

        $candidate = $base;
        $suffix = 2;

        while ($this->exists($candidate, $location)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function exists(string $fullSlug, Location $location): bool
    {
        return Location::query()
            ->where('full_slug', $fullSlug)
            ->when($location->exists, fn ($query) => $query->whereKeyNot($location->getKey()))
            ->exists();
    }
}
