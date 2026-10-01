<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    /**
     * Sign in as the seeded demo traveller with a single click. Only available
     * when demo mode is enabled (never in production by default).
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('roam.demo.enabled'), 404);

        $user = User::query()->where('email', config('roam.demo.user_email'))->first();

        if ($user === null) {
            return redirect()->route('login')->withErrors([
                'email' => 'Demo account not found. Run `php artisan db:seed` first.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
