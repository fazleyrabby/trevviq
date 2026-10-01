<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\LocationType;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TravelController extends Controller
{
    public function index(): View
    {
        $countries = Location::query()
            ->active()
            ->ofType(LocationType::Country)
            ->orderBy('name')
            ->get();

        return view('frontend.travel.index', ['countries' => $countries]);
    }

    public function show(Request $request, string $path): View|RedirectResponse
    {
        $location = Location::query()->where('full_slug', $path)->first();

        if ($location === null) {
            return $this->redirectOrFail($path);
        }

        $average = $location->reviews()->approved()->avg('rating');

        return view('frontend.travel.show', [
            'location' => $location,
            'breadcrumbs' => $location->breadcrumbs(),
            'children' => $location->children()
                ->active()
                ->orderBy('type')
                ->orderBy('name')
                ->limit(24)
                ->get(),
            'nearby' => $this->nearby($location),
            'reviews' => $location->reviews()->approved()->with('user')->latest()->limit(12)->get(),
            'reviewSummary' => [
                'count' => $location->reviews()->approved()->count(),
                'average' => $average !== null ? round((float) $average, 1) : null,
            ],
            'userReview' => $request->user()
                ? $location->reviews()->where('user_id', $request->user()->id)->first()
                : null,
            'videos' => $location->videos()
                ->published()
                ->with('user')
                ->latest('published_at')
                ->limit(6)
                ->get(),
            'events' => $location->events()
                ->published()
                ->upcoming()
                ->with('organizer')
                ->orderBy('starts_at')
                ->limit(6)
                ->get(),
        ]);
    }

    private function redirectOrFail(string $path): RedirectResponse
    {
        $redirect = Redirect::query()->where('old_path', '/travel/'.$path)->first();

        if ($redirect === null) {
            abort(404);
        }

        return redirect($redirect->new_path, $redirect->status_code);
    }

    /**
     * @return Collection<int, Location>
     */
    private function nearby(Location $location): Collection
    {
        if ($location->latitude === null || $location->longitude === null) {
            return collect();
        }

        $excludeIds = $location->ancestors()->pluck('id')->all();
        $excludeIds[] = $location->id;

        return Location::nearby(
            $location->latitude,
            $location->longitude,
            excludeIds: $excludeIds,
        );
    }
}
