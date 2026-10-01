<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoLikeController extends Controller
{
    public function toggle(Request $request, Video $video): RedirectResponse
    {
        DB::transaction(function () use ($request, $video): void {
            $existing = VideoLike::query()
                ->where('user_id', $request->user()->id)
                ->where('video_id', $video->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
            } else {
                $video->likes()->create(['user_id' => $request->user()->id]);
            }

            $video->forceFill(['like_count' => $video->likes()->count()])->save();
        });

        return back();
    }
}
