<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationImportBatch extends Model
{
    protected $fillable = [
        'source',
        'dataset_version',
        'checksum',
        'status',
        'countries',
        'total',
        'processed',
        'created',
        'updated',
        'skipped',
        'error',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'total' => 'integer',
            'processed' => 'integer',
            'created' => 'integer',
            'updated' => 'integer',
            'skipped' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
