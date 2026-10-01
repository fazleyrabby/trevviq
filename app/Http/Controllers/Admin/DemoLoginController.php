<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    /**
     * Sign in as the seeded demo administrator with a single click. Only
     * available when demo mode is enabled (never in production by default).
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(config('trevviq.demo.enabled'), 404);

        $admin = Admin::query()->where('email', config('trevviq.demo.admin_email'))->first();

        if ($admin === null) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Demo administrator not found. Run `php artisan db:seed` first.',
            ]);
        }

        Auth::guard('admin')->login($admin, true);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
