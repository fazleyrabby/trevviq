<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Jobs\RecomputeLocationIndexability;
use App\Services\Location\LocationSlugService;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'slug',
        'description',
        'search_index',
        'country_code',
        'region_code',
        'city_name',
        'address',
        'population',
        'latitude',
        'longitude',
        'timezone',
        'osm_id',
        'osm_type',
        'full_slug',
        'depth',
        'metadata',
        'is_verified',
        'is_active',
        'content_count',
        'indexable',
        'indexable_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LocationType::class,
            'metadata' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'depth' => 'integer',
            'population' => 'integer',
            'content_count' => 'integer',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'indexable' => 'boolean',
            'indexable_updated_at' => 'datetime',
        ];
    }

    /**
     * Public URLs resolve by the globally unique, human-readable path.
     */
    public function getRouteKeyName(): string
    {
        return 'full_slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Location $location): void {
            $slugs = app(LocationSlugService::class);

            $location->slug = $slugs->resolveSlug($location);

            if (blank($location->full_slug) || $location->isDirty(['slug', 'parent_id', 'name'])) {
                $location->full_slug = $slugs->uniqueFullSlug($location);
            }

            $parent = $location->parent;
            $location->depth = $parent ? $parent->depth + 1 : 0;
            $location->search_index = trim(implode(' ', array_filter([
                $parent?->search_index,
                $location->name,
            ])));
        });

        // Keep the SEO indexation gate in step when a location's own inputs
        // change (content phases dispatch this job directly for content).
        static::saved(function (Location $location): void {
            // isDirty (not wasChanged) because Laravel does not call
            // syncChanges() on insert, so wasChanged() is always false there.
            // The `saved` event fires before syncOriginal(), so isDirty is valid.
            if ($location->isDirty(['is_verified', 'description', 'content_count'])) {
                RecomputeLocationIndexability::dispatch($location->id);
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(TravellerLocation::class);
    }

    /**
     * @return HasMany<SavedLocation, $this>
     */
    public function saves(): HasMany
    {
        return $this->hasMany(SavedLocation::class);
    }

    public function isSavedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->relationLoaded('saves')
            ? $this->saves->contains('user_id', $user->id)
            : $this->saves()->where('user_id', $user->id)->exists();
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Ancestors ordered from the root down to the direct parent.
     *
     * @return Collection<int, Location>
     */
    public function ancestors(): Collection
    {
        $ancestors = new Collection;
        $node = $this->parent;
        $guard = 0;

        while ($node && $guard++ < 32) {
            $ancestors->prepend($node);
            $node = $node->parent;
        }

        return $ancestors;
    }

    /**
     * Breadcrumb trail including this location as the final crumb.
     *
     * @return Collection<int, Location>
     */
    public function breadcrumbs(): Collection
    {
        return $this->ancestors()->push($this);
    }

    public function isAdministrative(): bool
    {
        return $this->type->isAdministrative();
    }

    public function isPlace(): bool
    {
        return $this->type->isPlace();
    }

    /**
     * Great-circle distance in metres, or null when this location is unlocated.
     */
    public function distanceTo(float $latitude, float $longitude): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6371000.0;
        $dLat = deg2rad($latitude - $this->latitude);
        $dLng = deg2rad($longitude - $this->longitude);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(min(1.0, sqrt($a)));
    }

    /**
     * Bounding-box prefilter (uses the (latitude, longitude) index).
     */
    public function scopeWithinBoundingBox(Builder $query, float $latitude, float $longitude, int $meters): Builder
    {
        $latDelta = $meters / 111320;
        $lngDelta = $meters / (111320 * max(cos(deg2rad($latitude)), 0.000001));

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta]);
    }

    /**
     * Locations within a radius, ordered nearest first. The exact distance is
     * computed in PHP over the bounding-box candidate set so the same code path
     * works on every driver (including SQLite in tests).
     *
     * @param  array<int, int>  $excludeIds
     * @return Collection<int, Location>
     */
    public static function nearby(
        float $latitude,
        float $longitude,
        ?int $radiusMeters = null,
        ?int $limit = null,
        array $excludeIds = [],
    ): Collection {
        $radius = $radiusMeters ?? (int) config('trevviq.nearby.default_radius_meters');
        $max = (int) config('trevviq.nearby.max_limit');
        $take = min($limit ?? (int) config('trevviq.nearby.default_limit'), $max);

        return static::query()
            ->active()
            ->withinBoundingBox($latitude, $longitude, $radius)
            ->when($excludeIds, fn (Builder $query) => $query->whereNotIn('id', $excludeIds))
            ->get()
            ->each(function (Location $location) use ($latitude, $longitude): void {
                $location->setAttribute('distance_meters', $location->distanceTo($latitude, $longitude));
            })
            ->filter(fn (Location $location): bool => $location->distance_meters !== null && $location->distance_meters <= $radius)
            ->sortBy('distance_meters')
            ->take($take)
            ->values();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('indexable', true);
    }

    public function scopeOfType(Builder $query, LocationType|string ...$types): Builder
    {
        $values = array_map(
            static fn (LocationType|string $type): string => $type instanceof LocationType ? $type->value : $type,
            $types,
        );

        return $query->whereIn('type', $values);
    }
}
