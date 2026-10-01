<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\SavedLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationSaveController extends Controller
{
    public function toggle(Request $request, Location $location): RedirectResponse
    {
        DB::transaction(function () use ($request, $location): void {
            $existing = SavedLocation::query()
                ->where('user_id', $request->user()->id)
                ->where('location_id', $location->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
            } else {
                $location->saves()->create(['user_id' => $request->user()->id]);
            }
        });

        return back();
    }
}
