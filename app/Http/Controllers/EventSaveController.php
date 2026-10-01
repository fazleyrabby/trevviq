<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SavedEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventSaveController extends Controller
{
    public function toggle(Request $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $existing = SavedEvent::query()
                ->where('user_id', $request->user()->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
            } else {
                $event->saves()->create(['user_id' => $request->user()->id]);
            }
        });

        return back();
    }
}
