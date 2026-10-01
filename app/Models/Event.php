<?php

namespace App\Models;

use App\Enums\EventStatus;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $fillable = [
        'location_id',
        'organizer_id',
        'name',
        'slug',
        'description',
        'starts_at',
        'ends_at',
        'address',
        'latitude',
        'longitude',
        'website_url',
        'image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if (blank($event->slug)) {
                $event->slug = static::uniqueSlug((string) $event->name, $event);
            }
        });
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * @return HasMany<SavedEvent, $this>
     */
    public function saves(): HasMany
    {
        return $this->hasMany(SavedEvent::class);
    }

    public function isSavedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->relationLoaded('saves')
            ? $this->saves->contains('user_id', $user->id)
            : $this->saves()->where('user_id', $user->id)->exists();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Published);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('starts_at', '<', now());
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->organizer_id === $user->id;
    }

    private static function uniqueSlug(string $name, Event $event): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($event->exists, fn (Builder $query) => $query->whereKeyNot($event->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
