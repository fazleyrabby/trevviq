<?php

namespace App\Models;

use App\Enums\VisitSource;
use App\Enums\VisitVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravellerLocation extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'visited_at',
        'source',
        'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'date',
            'source' => VisitSource::class,
            'verification_status' => VisitVerification::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
