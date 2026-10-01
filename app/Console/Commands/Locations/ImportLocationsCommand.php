<?php

namespace App\Console\Commands\Locations;

use App\Services\Location\Import\GeoNamesImporter;
use App\Services\Location\Import\ImportResult;
use Illuminate\Console\Command;
use Throwable;

class ImportLocationsCommand extends Command
{
    protected $signature = 'locations:import
        {--source=geonames : Import source; only "geonames" is supported}
        {--file= : Absolute path to a GeoNames allCountries .txt file}
        {--country= : Comma-separated ISO-3166 alpha-2 allow-list (e.g. BD,JP)}
        {--fresh : Soft-deactivate existing geonames rows before importing}
        {--dry-run : Parse and report without writing to the database}';

    protected $description = 'Import the GeoNames administrative location tree';

    public function __construct(private readonly GeoNamesImporter $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $source = strtolower((string) $this->option('source'));

        if ($source !== 'geonames') {
            $this->error("Unsupported source [{$source}]. Only [geonames] is implemented.");

            return self::FAILURE;
        }

        $file = trim((string) $this->option('file'));

        if ($file === '' || ! is_file($file) || ! is_readable($file)) {
            $this->error($file === ''
                ? 'No file supplied. Pass --file=/absolute/path/to/allCountries.txt.'
                : "GeoNames file not found or unreadable: {$file}");

            return self::FAILURE;
        }

        $countries = $this->parseCountries();
        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            'Importing %s%s into %s.',
            $file,
            $countries === null ? '' : ' (countries: '.implode(', ', $countries).')',
            $dryRun ? 'dry-run (no writes)' : 'the database',
        ));

        try {
            $result = $this->importer->import(
                file: $file,
                countries: $countries,
                fresh: (bool) $this->option('fresh'),
                dryRun: $dryRun,
                onProgress: function (ImportResult $result): void {
                    $this->info('  '.$result->summary());
                },
            );
        } catch (Throwable $exception) {
            $this->error('Import failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Done: '.$result->summary().'.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>|null
     */
    private function parseCountries(): ?array
    {
        $raw = trim((string) $this->option('country'));

        if ($raw === '') {
            return null;
        }

        $countries = array_values(array_filter(
            array_map(static fn (string $country): string => strtoupper(trim($country)), explode(',', $raw)),
            static fn (string $country): bool => $country !== '',
        ));

        return $countries === [] ? null : $countries;
    }
}
