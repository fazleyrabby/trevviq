<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventSaveController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\ExploreController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\TravelController;
use App\Http\Controllers\Frontend\TravellerController;
use App\Http\Controllers\LocationSaveController;
use App\Http\Controllers\MyReviewController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewHelpfulController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SavedController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TravelHistoryController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoLikeController;
use App\Http\Controllers\VideoSaveController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [HomeController::class, 'index'])->name('home');

// SEO surface: robots + chunked sitemap (Section 79)
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-{page}.xml', [SitemapController::class, 'page'])
    ->whereNumber('page')
    ->name('sitemap.page');

// Location discovery
Route::get('/travel', [TravelController::class, 'index'])->name('travel.index');
Route::get('/travel/{path}', [TravelController::class, 'show'])
    ->where('path', '.*')
    ->name('travel.show');
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore.index');

// Public traveller profiles
Route::get('/traveller/{username}', [TravellerController::class, 'show'])->name('traveller.show');

// Video feed (Section 18)
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/video/{video}', [VideoController::class, 'show'])->name('videos.show');

// Events (Section 22) — /events/create must precede the slug wildcard.
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/create', [EventController::class, 'create'])->middleware('auth')->name('events.create');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    // One-click demo login (enabled outside production).
    Route::post('/demo-login', [DemoLoginController::class, 'store'])
        ->middleware('throttle:demo-login')
        ->name('demo.login');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/saved', [SavedController::class, 'index'])->name('saved.index');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/become-traveller', [ProfileController::class, 'becomeTraveller'])->name('profile.become-traveller');

    // Traveller reviews (Section 21).
    Route::post('/reviews', [ReviewController::class, 'store'])
        ->middleware('throttle:reviews')
        ->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/helpful', [ReviewHelpfulController::class, 'toggle'])
        ->middleware('throttle:helpful')
        ->name('reviews.helpful');
    Route::post('/reviews/{review}/report', [ReportController::class, 'storeReview'])
        ->middleware('throttle:reports')
        ->name('reviews.report');
    Route::get('/reviews', [MyReviewController::class, 'index'])->name('reviews.index');

    // Travel history (Section 20).
    Route::get('/travel-history', [TravelHistoryController::class, 'index'])->name('travel-history.index');
    Route::post('/travel-history', [TravelHistoryController::class, 'store'])->name('travel-history.store');

    // Saved Locations
    Route::post('/locations/{location}/save', [LocationSaveController::class, 'toggle'])
        ->middleware('throttle:likes-saves')
        ->name('locations.save');
    Route::delete('/travel-history/{visit}', [TravelHistoryController::class, 'destroy'])->name('travel-history.destroy');

    // Following (Section 24).
    Route::post('/travellers/{user}/follow', [FollowController::class, 'store'])
        ->middleware('throttle:follows')
        ->name('follows.store');
    Route::delete('/travellers/{user}/follow', [FollowController::class, 'destroy'])->name('follows.destroy');

    // Video uploads & interactions (Sections 16, 18).
    Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoController::class, 'store'])
        ->middleware('throttle:video-upload')
        ->name('videos.store');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    Route::post('/videos/{video}/like', [VideoLikeController::class, 'toggle'])
        ->middleware('throttle:likes-saves')
        ->name('videos.like');
    Route::post('/videos/{video}/save', [VideoSaveController::class, 'toggle'])
        ->middleware('throttle:likes-saves')
        ->name('videos.save');
    Route::post('/videos/{video}/share', [VideoController::class, 'share'])->name('videos.share');
    Route::post('/videos/{video}/report', [ReportController::class, 'storeVideo'])
        ->middleware('throttle:reports')
        ->name('videos.report');

    // Events (Section 22).
    Route::post('/events', [EventController::class, 'store'])
        ->middleware('throttle:events')
        ->name('events.store');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/report', [ReportController::class, 'storeEvent'])
        ->middleware('throttle:reports')
        ->name('events.report');
    Route::post('/events/{event}/save', [EventSaveController::class, 'toggle'])
        ->middleware('throttle:likes-saves')
        ->name('events.save');
});

// Admin CMS
Route::prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));
