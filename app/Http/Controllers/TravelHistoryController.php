<?php

namespace App\Http\Controllers;

use App\Enums\VisitSource;
use App\Enums\VisitVerification;
use App\Http\Requests\StoreVisitRequest;
use App\Models\TravellerLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravelHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $visits = $request->user()->visits()
            ->with('location')
            ->orderByDesc('visited_at')
            ->orderByDesc('id')
            ->get();

        return view('frontend.travel-history.index', ['visits' => $visits]);
    }

    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $request->user()->visits()->updateOrCreate(
            ['location_id' => $request->integer('location_id')],
            [
                'visited_at' => $request->date('visited_at'),
                'source' => VisitSource::Claimed,
                'verification_status' => VisitVerification::Unverified,
            ],
        );

        return redirect()->back()->with('status', 'visit-added');
    }

    public function destroy(Request $request, TravellerLocation $visit): RedirectResponse
    {
        abort_unless($visit->user_id === $request->user()->id, 403);

        $visit->delete();

        return redirect()->back()->with('status', 'visit-removed');
    }
}
