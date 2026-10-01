<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportModerationController extends Controller
{
    /**
     * Display the report moderation queue.
     */
    public function index(Request $request): View
    {
        $statusQuery = $request->query('status');
        $statusEnum = ReportStatus::tryFrom((string) $statusQuery);

        $reports = Report::query()
            ->with(['reporter', 'reportable'])
            ->when($statusEnum, fn ($query) => $query->where('status', $statusEnum))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'status' => is_string($statusQuery) ? $statusQuery : null,
            'statuses' => ReportStatus::cases(),
        ]);
    }

    /**
     * Update the status of a report.
     */
    public function update(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(ReportStatus::class)],
        ]);

        $report->update([
            'status' => $data['status'],
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report updated.');
    }
}
