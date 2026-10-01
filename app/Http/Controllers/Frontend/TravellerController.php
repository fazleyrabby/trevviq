<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TravellerController extends Controller
{
    /**
     * Public traveller profile resolved by username.
     */
    public function show(Request $request, string $username): View
    {
        $user = User::query()->where('username', $username)->firstOrFail();

        $reviews = $user->reviews()
            ->approved()
            ->with('location')
            ->latest()
            ->limit(10)
            ->get();

        $visits = $user->visits()
            ->with('location')
            ->orderByDesc('visited_at')
            ->orderByDesc('id')
            ->limit(24)
            ->get();

        $stats = [
            'places' => $user->visits()->count(),
            'reviews' => $user->reviews()->approved()->count(),
            'followers' => $user->followers()->count(),
            'following' => $user->following()->count(),
        ];

        $isFollowing = $request->user() !== null && $request->user()->isFollowing($user);

        $isSelf = $request->user()?->is($user) ?? false;

        return view('frontend.traveller.show', [
            'user' => $user,
            'reviews' => $reviews,
            'visits' => $visits,
            'stats' => $stats,
            'isFollowing' => $isFollowing,
            'isSelf' => $isSelf,
        ]);
    }
}
