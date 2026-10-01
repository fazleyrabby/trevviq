<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\StoreReportRequest;
use App\Models\Event;
use App\Models\Report;
use App\Models\Review;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function storeReview(StoreReportRequest $request, Review $review): RedirectResponse
    {
        $alreadyReported = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', Review::class)
            ->where('reportable_id', $review->id)
            ->where('status', ReportStatus::Open->value)
            ->exists();

        if ($alreadyReported) {
            return back()->withFragment('reviews')->with('status', 'report-duplicate');
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
            'status' => ReportStatus::Open,
        ]);

        return back()->withFragment('reviews')->with('status', 'report-submitted');
    }

    public function storeVideo(StoreReportRequest $request, Video $video): RedirectResponse
    {
        $alreadyReported = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', Video::class)
            ->where('reportable_id', $video->id)
            ->where('status', ReportStatus::Open->value)
            ->exists();

        if ($alreadyReported) {
            return back()->with('status', 'report-duplicate');
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => Video::class,
            'reportable_id' => $video->id,
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
            'status' => ReportStatus::Open,
        ]);

        return back()->with('status', 'report-submitted');
    }

    public function storeEvent(StoreReportRequest $request, Event $event): RedirectResponse
    {
        $alreadyReported = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', Event::class)
            ->where('reportable_id', $event->id)
            ->where('status', ReportStatus::Open->value)
            ->exists();

        if ($alreadyReported) {
            return back()->with('status', 'report-duplicate');
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => Event::class,
            'reportable_id' => $event->id,
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
            'status' => ReportStatus::Open,
        ]);

        return back()->with('status', 'report-submitted');
    }
}
