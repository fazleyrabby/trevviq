<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VideoStatus;
use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoModerationController extends Controller
{
    /**
     * Display the video moderation queue.
     */
    public function index(Request $request): View
    {
        $statusQuery = $request->query('status');
        $statusEnum = VideoStatus::tryFrom((string) $statusQuery);

        $videos = Video::query()
            ->with(['user', 'location'])
            ->when($statusEnum, fn ($query) => $query->where('status', $statusEnum))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.videos.index', [
            'videos' => $videos,
            'status' => is_string($statusQuery) ? $statusQuery : null,
            'statuses' => VideoStatus::cases(),
        ]);
    }

    /**
     * Update the status of a video.
     */
    public function update(Request $request, Video $video): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(VideoStatus::class)],
        ]);

        $payload = ['status' => $data['status']];

        if ($data['status'] === VideoStatus::Published->value && $video->published_at === null) {
            $payload['published_at'] = now();
        }

        $video->update($payload);

        if ($video->location) {
            app(LocationContentService::class)->recompute($video->location);
        }

        return back()->with('success', 'Video status updated.');
    }
}
