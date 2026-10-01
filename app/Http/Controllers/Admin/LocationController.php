<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LocationType;
use App\Http\Controllers\Controller;
use App\Jobs\RecomputeLocationIndexability;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $type = $request->query('type');
        $typeEnum = is_string($type) ? LocationType::tryFrom($type) : null;

        $locations = Location::query()
            ->when($typeEnum, fn ($q) => $q->where('type', $typeEnum))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('full_slug', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.locations.index', [
            'locations' => $locations,
            'search' => $search,
            'type' => $type,
            'types' => LocationType::cases(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'is_verified' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        foreach ($data as $key => $value) {
            $location->{$key} = $value;
        }

        $verifiedChanged = $location->isDirty('is_verified');

        $location->saveQuietly();

        if ($verifiedChanged) {
            RecomputeLocationIndexability::dispatch($location->id);
        }

        return back()->with('success', 'Location updated.');
    }
}
