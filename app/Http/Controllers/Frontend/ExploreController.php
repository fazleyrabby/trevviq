<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\LocationType;
use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const FILTERS = [
        'city' => 'Cities',
        'country' => 'Countries',
        'region' => 'Regions',
        'place' => 'Places',
    ];

    public function index(Request $request): View
    {
        $activeFilter = $this->resolveFilter($request->query('type'));

        $locations = Location::query()
            ->active()
            ->ofType(...$this->typesFor($activeFilter))
            ->orderByDesc('content_count')
            ->orderByDesc('population')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('frontend.explore.index', [
            'locations' => $locations,
            'activeFilter' => $activeFilter,
            'filters' => self::FILTERS,
        ]);
    }

    private function resolveFilter(mixed $type): string
    {
        return is_string($type) && array_key_exists($type, self::FILTERS) ? $type : 'city';
    }

    /**
     * @return array<int, LocationType>
     */
    private function typesFor(string $filter): array
    {
        return match ($filter) {
            'country' => [LocationType::Country],
            'region' => [LocationType::Region],
            'place' => LocationType::places(),
            default => [LocationType::City],
        };
    }
}
