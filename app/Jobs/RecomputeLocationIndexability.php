<?php

namespace App\Jobs;

use App\Models\Location;
use App\Services\Location\LocationIndexationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Recomputes a single location's indexation gate after its approved content
 * (videos, reviews, events) is added or removed. Dispatched by the future
 * content phases so the flag stays in step without side effects of its own.
 */
final class RecomputeLocationIndexability implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $locationId) {}

    public function handle(LocationIndexationService $indexation): void
    {
        $location = Location::find($this->locationId);

        if ($location === null) {
            return;
        }

        $indexation->recompute($location);
    }
}
