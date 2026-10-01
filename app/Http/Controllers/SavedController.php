<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $savedLocations = $user->savedLocations()
            ->with(['location.type', 'location.parent'])
            ->latest()
            ->get();

        $savedVideos = $user->savedVideos()
            ->with(['video.location', 'video.user'])
            ->latest()
            ->get();

        $savedEvents = $user->savedEvents()
            ->with(['event.location'])
            ->latest()
            ->get();

        return view('frontend.saved.index', [
            'savedLocations' => $savedLocations,
            'savedVideos' => $savedVideos,
            'savedEvents' => $savedEvents,
        ]);
    }
}
