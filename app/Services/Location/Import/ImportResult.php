<?php

namespace App\Services\Location\Import;

/**
 * Mutable counters accumulated over a single GeoNames import run.
 *
 * `processed` is the sum of every considered row: created + updated + skipped.
 */
final class ImportResult
{
    public int $processed = 0;

    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function total(): int
    {
        return $this->processed;
    }

    public function summary(): string
    {
        return sprintf(
            'processed %d, created %d, updated %d, skipped %d',
            $this->processed,
            $this->created,
            $this->updated,
            $this->skipped,
        );
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total(),
            'processed' => $this->processed,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }
}
