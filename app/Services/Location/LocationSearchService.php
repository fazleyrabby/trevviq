<?php

namespace App\Services\Location;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationSearchService
{
    /**
     * Ranked, driver-portable location search.
     *
     * Every whitespace-separated token must appear in the denormalized
     * `search_index` (which chains ancestor names), so queries such as
     * "paris france" resolve. Results are ordered by exact-name match, prefix
     * match, substring match, then traveller content volume.
     */
    public function search(string $term, ?LocationType $type = null, ?int $perPage = null): LengthAwarePaginator
    {
        $term = trim($term);
        $perPage = min(max($perPage ?? (int) config('trevviq.search.default_per_page'), 1), 50);
        $lower = mb_strtolower($term);

        $query = Location::query()->active();

        if ($term !== '') {
            foreach ($this->tokens($lower) as $token) {
                $query->whereRaw('LOWER(search_index) LIKE ?', ['%'.$token.'%']);
            }
        }

        if ($type !== null) {
            $query->ofType($type);
        }

        $query->orderByRaw(
            'CASE WHEN LOWER(name) = ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 ELSE 2 END',
            [$lower, $lower.'%'],
        );

        // Administrative hubs rank above points of interest, then popularity.
        $query->orderByRaw(
            'CASE type WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 ELSE 4 END',
            [
                LocationType::Country->value,
                LocationType::City->value,
                LocationType::Region->value,
                LocationType::District->value,
            ],
        );

        $query->orderByDesc('content_count')->orderBy('name');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $lower): array
    {
        return array_values(array_filter(
            preg_split('/\s+/', $lower) ?: [],
            static fn (string $token): bool => $token !== '',
        ));
    }
}
