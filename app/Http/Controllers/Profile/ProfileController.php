<?php

namespace App\Http\Controllers\Profile;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('profile.show', compact('user'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        unset($validated['avatar']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $validated['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($validated);
        $user->save();

        return redirect()->route('profile.show')->with('status', 'profile-updated');
    }

    public function becomeTraveller(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isViewer()) {
            return redirect()->route('profile.show');
        }

        $user->role = UserRole::Traveller;
        $user->save();

        return redirect()->route('profile.show')->with('status', 'now-a-traveller');
    }
}
