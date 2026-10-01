<?php

namespace App\Providers;

use App\Contracts\VideoProcessor;
use App\Models\Admin;
use App\Services\Video\FakeVideoProcessor;
use App\Services\Video\FfmpegVideoProcessor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Video processing is swappable: FFmpeg in production, a deterministic
        // fake in tests/local so the pipeline is testable without the binary.
        $this->app->bind(VideoProcessor::class, function (): VideoProcessor {
            if (config('roam.video.processor') === 'ffmpeg') {
                return new FfmpegVideoProcessor(
                    (string) config('roam.video.ffmpeg_bin'),
                    (string) config('roam.video.ffprobe_bin'),
                );
            }

            return new FakeVideoProcessor;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Baseline account password policy. Kept deterministic (no remote
        // breach checks) so validation is fast and offline-friendly.
        Password::defaults(fn () => Password::min(8));

        $this->configureRateLimiting();
        $this->configureAuthorization();
    }

    private function configureRateLimiting(): void
    {
        // Admin login: 5 attempts per minute per email + IP combination.
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Traveller login: 5 attempts per 15 minutes per email + IP (Section 81).
        RateLimiter::for('login', fn (Request $request) => Limit::perMinutes(15, 5)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Registration: 5 per hour per IP (Section 81).
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(5)
            ->by($request->ip()));

        // Demo one-click login: modest per-IP cap.
        RateLimiter::for('demo-login', fn (Request $request) => Limit::perMinute(10)
            ->by($request->ip()));

        // Password reset requests: 3 per hour per email (Section 81).
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perHour(3)
            ->by(strtolower((string) $request->input('email'))));

        // Traveller content actions (Section 81). Keys fall back to IP for guests.
        RateLimiter::for('reviews', fn (Request $request) => Limit::perDay(5)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('reports', fn (Request $request) => Limit::perDay(10)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('helpful', fn (Request $request) => Limit::perHour(300)
            ->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('follows', fn (Request $request) => Limit::perDay(50)
            ->by($request->user()?->id ?: $request->ip()));

        // Upload init: 3/hour and 10/day per user (Section 81).
        RateLimiter::for('video-upload', fn (Request $request) => [
            Limit::perHour(3)->by($request->user()?->id ?: $request->ip()),
            Limit::perDay(10)->by($request->user()?->id ?: $request->ip()),
        ]);

        // Likes & saves: 300/hour per user (Section 81).
        RateLimiter::for('likes-saves', fn (Request $request) => Limit::perHour(300)
            ->by($request->user()?->id ?: $request->ip()));

        // Event creation: modest per-user cap.
        RateLimiter::for('events', fn (Request $request) => Limit::perDay(5)
            ->by($request->user()?->id ?: $request->ip()));

        // Public lead forms: modest hourly and daily caps per IP.
        RateLimiter::for('public-forms', fn (Request $request) => [
            Limit::perHour(10)->by($request->ip()),
            Limit::perDay(30)->by($request->ip()),
        ]);
    }

    private function configureAuthorization(): void
    {
        // Baseline authorization layer. Every authenticated admin currently has
        // full access; a `super_admin` role will be able to do everything as
        // more granular roles are introduced. The callback runs for EVERY
        // authenticated user (including travellers), so it must not be typed to
        // Admin or regular users would hit a TypeError.
        Gate::before(fn ($user) => $user instanceof Admin && ($user->role ?? null) === 'super_admin' ? true : null);

        Gate::define('manage-settings', fn (Admin $admin) => in_array($admin->role ?? 'admin', ['admin', 'super_admin'], true));
        Gate::define('manage-content', fn (Admin $admin) => in_array($admin->role ?? 'admin', ['admin', 'super_admin'], true));
        Gate::define('view-inbox', fn (Admin $admin) => in_array($admin->role ?? 'admin', ['admin', 'super_admin'], true));
    }
}
