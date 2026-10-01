<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Enums\VideoStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Location;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Models\Video;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'locations' => Location::count(),
            'videos' => Video::count(),
            'reviews' => Review::count(),
            'events' => 0,
            'travellers' => User::where('role', UserRole::Traveller)->count(),
            'pending_moderation' => Review::where('status', ReviewStatus::Pending)->count()
                + Video::where('status', VideoStatus::Pending)->count()
                + Report::where('status', ReportStatus::Open)->count(),
        ];

        $recentActivity = collect();
        $adminsCount = Admin::count();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'adminsCount'));
    }
}
