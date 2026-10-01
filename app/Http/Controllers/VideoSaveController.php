<?php

namespace App\Http\Controllers;

use App\Models\SavedVideo;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoSaveController extends Controller
{
    public function toggle(Request $request, Video $video): RedirectResponse
    {
        DB::transaction(function () use ($request, $video): void {
            $existing = SavedVideo::query()
                ->where('user_id', $request->user()->id)
                ->where('video_id', $video->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
            } else {
                $video->saves()->create(['user_id' => $request->user()->id]);
            }
        });

        return back();
    }
}
