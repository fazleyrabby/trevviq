<?php

namespace App\Http\Controllers;

use App\Enums\VideoStatus;
use App\Enums\VideoVisibility;
use App\Http\Requests\StoreVideoRequest;
use App\Jobs\ProcessVideo;
use App\Models\Video;
use App\Services\Location\LocationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $videos = Video::query()
            ->published()
            ->with(['user', 'location'])
            ->latest('published_at')
            ->paginate(12);

        return view('frontend.videos.index', ['videos' => $videos]);
    }

    public function create(): View
    {
        return view('frontend.videos.create');
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $file = $request->file('video');
        $disk = config('trevviq.video.disk');

        $path = $file->store('videos/originals', $disk);

        $video = $request->user()->videos()->create([
            'location_id' => $request->integer('location_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'visibility' => $request->input('visibility', VideoVisibility::Public->value),
            'original_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => VideoStatus::Pending,
        ]);

        ProcessVideo::dispatch($video->id);

        return redirect()
            ->route('videos.show', $video)
            ->with('status', 'video-uploaded');
    }

    public function show(Request $request, Video $video): View
    {
        $isPublished = $video->status === VideoStatus::Published
            && $video->visibility === VideoVisibility::Public;

        if (! $isPublished && ! $video->isOwnedBy($request->user())) {
            abort(404);
        }

        $key = "video-viewed:{$video->id}";

        if ($isPublished && ! $request->session()->has($key)) {
            $video->increment('view_count');
            $request->session()->put($key, true);
        }

        $video->load(['user', 'location']);

        return view('frontend.videos.show', ['video' => $video]);
    }

    public function destroy(Request $request, Video $video): RedirectResponse
    {
        abort_unless($video->isOwnedBy($request->user()), 403);

        $video->forceFill(['status' => VideoStatus::Deleted])->save();

        if ($video->location_id !== null) {
            app(LocationContentService::class)->recompute($video->location);
        }

        return redirect()
            ->route('videos.index')
            ->with('status', 'video-deleted');
    }

    public function share(Request $request, Video $video): RedirectResponse
    {
        $video->increment('share_count');

        return back()->with('status', 'video-shared');
    }
}
