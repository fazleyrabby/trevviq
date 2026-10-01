<?php

namespace App\Jobs;

use App\Contracts\VideoProcessor;
use App\Enums\VideoStatus;
use App\Models\Video;
use App\Services\Location\LocationContentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $videoId) {}

    public function handle(VideoProcessor $processor): void
    {
        $video = Video::query()->find($this->videoId);

        if ($video === null || $video->status === VideoStatus::Deleted) {
            return;
        }

        $video->forceFill(['status' => VideoStatus::Processing])->save();

        $result = $processor->process($video);

        $maxDuration = (int) config('trevviq.video.max_duration');
        $rejected = $result->duration > $maxDuration;

        if ($rejected) {
            $status = VideoStatus::Rejected;
        } else {
            $status = config('trevviq.video.auto_publish')
                ? VideoStatus::Published
                : VideoStatus::Pending;
        }

        $video->forceFill([
            'duration' => $result->duration,
            'width' => $result->width,
            'height' => $result->height,
            'processed_path' => $result->processedPath,
            'thumbnail_path' => $result->thumbnailPath,
            'stored_bytes' => $result->storedBytes,
            'processing_ms' => $result->processingMs,
            'status' => $status,
            'published_at' => $status === VideoStatus::Published ? now() : null,
        ])->save();

        if ($video->location_id !== null) {
            app(LocationContentService::class)->recompute($video->location);
        }
    }
}
