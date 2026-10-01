<?php

namespace App\Contracts;

use App\Models\Video;
use App\Services\Video\FakeVideoProcessor;
use App\Services\Video\FfmpegVideoProcessor;
use App\Support\Video\VideoProcessingResult;

/**
 * Turns an uploaded original into server-verified metadata plus an optimized
 * rendition and thumbnail. Implementations: {@see FfmpegVideoProcessor}
 * (real) and {@see FakeVideoProcessor} (tests/local).
 */
interface VideoProcessor
{
    public function process(Video $video): VideoProcessingResult;
}
