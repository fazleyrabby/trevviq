<?php

namespace App\Console\Commands\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Services\Location\LocationContentService;
use Illuminate\Console\Command;

class ModerateReviewCommand extends Command
{
    protected $signature = 'reviews:moderate
        {review : The review ID}
        {--status=approved : pending|approved|rejected|blocked}';

    protected $description = "Change a review's moderation status";

    public function handle(): int
    {
        $status = ReviewStatus::tryFrom((string) $this->option('status'));

        if ($status === null) {
            $valid = implode(', ', array_map(
                static fn (ReviewStatus $case): string => $case->value,
                ReviewStatus::cases(),
            ));

            $this->error("Invalid status. Valid values: {$valid}.");

            return self::FAILURE;
        }

        $review = Review::query()->find($this->argument('review'));

        if ($review === null) {
            $this->error("Review {$this->argument('review')} not found.");

            return self::FAILURE;
        }

        $review->status = $status;
        $review->save();

        app(LocationContentService::class)->recompute($review->location);

        $this->info("Review {$review->id} set to {$status->value}.");

        return self::SUCCESS;
    }
}
