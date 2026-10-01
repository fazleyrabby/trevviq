<?php

namespace App\Console\Commands\Locations;

use App\Services\Location\LocationIndexationService;
use Illuminate\Console\Command;

class ReindexLocationsCommand extends Command
{
    protected $signature = 'locations:reindex
        {--dry-run : Report what would change without writing}';

    protected $description = 'Rebuild the location indexation gate';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $changed = app(LocationIndexationService::class)->recomputeAll($dryRun);

        $this->info($dryRun
            ? sprintf('%d location(s) would become indexable.', $changed)
            : sprintf('%d location(s) updated.', $changed));

        return self::SUCCESS;
    }
}
