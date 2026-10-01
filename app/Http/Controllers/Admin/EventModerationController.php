<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventModerationController extends Controller
{
    /**
     * Display the event moderation queue.
     */
    public function index(Request $request): View
    {
        $statusQuery = $request->query('status');
        $statusEnum = EventStatus::tryFrom((string) $statusQuery);

        $events = Event::query()
            ->with(['location', 'organizer'])
            ->when($statusEnum, fn ($query) => $query->where('status', $statusEnum))
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'status' => is_string($statusQuery) ? $statusQuery : null,
            'statuses' => EventStatus::cases(),
        ]);
    }

    /**
     * Update the status of an event.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(EventStatus::class)],
        ]);

        $event->update(['status' => $data['status']]);

        if ($data['status'] === EventStatus::Published->value && $event->location !== null) {
            app(LocationContentService::class)->recompute($event->location);
        }

        return back()->with('success', 'Event status updated.');
    }
}
