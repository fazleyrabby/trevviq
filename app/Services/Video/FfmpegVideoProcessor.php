<?php

namespace App\Services\Video;

use App\Contracts\VideoProcessor;
use App\Models\Video;
use App\Support\Video\VideoProcessingResult;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Real processor: FFprobe for server-side truth (duration/resolution/codec) and
 * FFmpeg for a bounded 1080p rendition plus a poster frame (Section 82).
 */
class FfmpegVideoProcessor implements VideoProcessor
{
    public function __construct(
        private readonly string $ffmpegBin = 'ffmpeg',
        private readonly string $ffprobeBin = 'ffprobe',
    ) {}

    public function process(Video $video): VideoProcessingResult
    {
        $disk = Storage::disk($video->disk());
        $input = $disk->path((string) $video->original_path);

        $probe = new Process([
            $this->ffprobeBin, '-v', 'quiet', '-print_format', 'json',
            '-show_format', '-show_streams', $input,
        ]);
        $probe->run();

        if (! $probe->isSuccessful()) {
            throw new RuntimeException('ffprobe failed: '.$probe->getErrorOutput());
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($probe->getOutput() ?: '[]', true) ?: [];
        $stream = collect($data['streams'] ?? [])->firstWhere('codec_type', 'video') ?? [];

        $duration = (int) round((float) ($data['format']['duration'] ?? 0));
        $width = (int) ($stream['width'] ?? 0);
        $height = (int) ($stream['height'] ?? 0);

        $processedPath = "videos/{$video->id}/optimized.mp4";
        $thumbnailPath = "videos/{$video->id}/thumb.jpg";

        $startedAt = microtime(true);

        $encode = new Process([
            $this->ffmpegBin, '-y', '-i', $input,
            '-vf', "scale='min(1080,iw)':-2",
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '23',
            '-c:a', 'aac', '-b:a', '128k', '-movflags', '+faststart',
            $disk->path($processedPath),
        ]);
        $encode->setTimeout(600)->run();

        if (! $encode->isSuccessful()) {
            throw new RuntimeException('ffmpeg encode failed: '.$encode->getErrorOutput());
        }

        $poster = new Process([
            $this->ffmpegBin, '-y', '-ss', '1', '-i', $input,
            '-frames:v', '1', $disk->path($thumbnailPath),
        ]);
        $poster->setTimeout(120)->run();

        $processingMs = (int) round((microtime(true) - $startedAt) * 1000);

        return new VideoProcessingResult(
            duration: $duration,
            width: $width,
            height: $height,
            processedPath: $processedPath,
            thumbnailPath: $disk->exists($thumbnailPath) ? $thumbnailPath : null,
            storedBytes: $disk->exists($processedPath) ? (int) $disk->size($processedPath) : null,
            processingMs: $processingMs,
        );
    }
}
