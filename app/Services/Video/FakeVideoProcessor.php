<?php

namespace App\Services\Video;

use App\Contracts\VideoProcessor;
use App\Models\Video;
use App\Support\Video\VideoProcessingResult;
use Illuminate\Support\Facades\Storage;

/**
 * Deterministic processor used in tests and local development when FFmpeg is
 * not available. Reads a real duration from the video metadata when the caller
 * has stubbed one, otherwise reports a short clip.
 */
class FakeVideoProcessor implements VideoProcessor
{
    public function process(Video $video): VideoProcessingResult
    {
        $disk = Storage::disk($video->disk());

        $processedPath = "videos/{$video->id}/optimized.mp4";
        $thumbnailPath = "videos/{$video->id}/thumb.png";

        $disk->put($processedPath, 'fake-processed-video');
        $disk->put($thumbnailPath, (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        ));

        return new VideoProcessingResult(
            duration: (int) ($video->duration ?: 30),
            width: $video->width ?: 1080,
            height: $video->height ?: 1920,
            processedPath: $processedPath,
            thumbnailPath: $thumbnailPath,
            storedBytes: $video->file_size,
            processingMs: 5,
        );
    }
}
