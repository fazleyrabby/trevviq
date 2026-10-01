<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DemoLoginController;
use App\Http\Controllers\Admin\EventModerationController;
use App\Http\Controllers\Admin\ImportBatchController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportModerationController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VideoModerationController;
use Illuminate\Support\Facades\Route;

// Guest admin routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login.submit');

    // One-click demo admin login (enabled outside production).
    Route::post('/demo-login', [DemoLoginController::class, 'store'])->name('demo-login');
});

// Authenticated admin routes
Route::middleware('auth:admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Discovery
    |--------------------------------------------------------------------------
    */
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::patch('/locations/{location:id}', [LocationController::class, 'update'])->name('locations.update');
    Route::get('/imports', [ImportBatchController::class, 'index'])->name('imports.index');

    /*
    |--------------------------------------------------------------------------
    | Moderation queues
    |--------------------------------------------------------------------------
    */
    Route::get('/videos', [VideoModerationController::class, 'index'])->name('videos.index');
    Route::patch('/videos/{video}', [VideoModerationController::class, 'update'])->name('videos.update');

    Route::get('/events', [EventModerationController::class, 'index'])->name('events.index');
    Route::patch('/events/{event}', [EventModerationController::class, 'update'])->name('events.update');

    Route::get('/reviews', [ReviewModerationController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}', [ReviewModerationController::class, 'update'])->name('reviews.update');

    Route::get('/reports', [ReportModerationController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [ReportModerationController::class, 'update'])->name('reports.update');

    /*
    |--------------------------------------------------------------------------
    | Community
    |--------------------------------------------------------------------------
    */
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});
