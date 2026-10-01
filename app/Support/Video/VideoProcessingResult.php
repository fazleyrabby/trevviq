<?php

namespace App\Support\Video;

final readonly class VideoProcessingResult
{
    public function __construct(
        public int $duration,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $processedPath = null,
        public ?string $thumbnailPath = null,
        public ?int $storedBytes = null,
        public ?int $processingMs = null,
    ) {}
}
