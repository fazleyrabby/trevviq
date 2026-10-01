<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use App\Notifications\NewFollower;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return redirect()->back();
        }

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        // Send notification if not already notified recently or just assume they might want it
        $user->notify(new NewFollower($request->user()));

        return redirect()->back()->with('status', 'followed');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $request->user()->following()->detach($user->id);

        return redirect()->back()->with('status', 'unfollowed');
    }
}
