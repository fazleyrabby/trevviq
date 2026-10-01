<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\Location;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display the upcoming published events.
     */
    public function index(Request $request): View
    {
        $events = Event::query()
            ->published()
            ->upcoming()
            ->with('location')
            ->orderBy('starts_at')
            ->paginate(12);

        return view('frontend.events.index', ['events' => $events]);
    }

    /**
     * Show the event submission form.
     */
    public function create(Request $request): View
    {
        return view('frontend.events.create');
    }

    /**
     * Store a newly created event.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $location = Location::query()->findOrFail($request->integer('location_id'));

        $status = config('trevviq.events.auto_approve')
            ? EventStatus::Published
            : EventStatus::Pending;

        $validated = $request->validated();

        $event = $request->user()->events()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'address' => $validated['address'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'location_id' => $location->id,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'status' => $status,
        ]);

        if ($status === EventStatus::Published) {
            app(LocationContentService::class)->recompute($location);
        }

        return redirect()
            ->route('events.show', $event)
            ->with('status', 'event-created');
    }

    /**
     * Display a single event, hiding unpublished events from non-owners.
     */
    public function show(Request $request, Event $event): View
    {
        if ($event->status !== EventStatus::Published && ! $event->isOwnedBy($request->user())) {
            abort(404);
        }

        $event->load(['location', 'organizer']);

        return view('frontend.events.show', ['event' => $event]);
    }

    /**
     * Delete an event owned by the current user.
     */
    public function destroy(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->isOwnedBy($request->user()), 403);

        $location = $event->location;

        $event->delete();

        if ($location !== null) {
            app(LocationContentService::class)->recompute($location);
        }

        return redirect()
            ->route('events.index')
            ->with('status', 'event-deleted');
    }
}
