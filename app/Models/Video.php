<?php

namespace App\Models;

use App\Enums\VideoStatus;
use App\Enums\VideoVisibility;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'description',
        'original_path',
        'processed_path',
        'thumbnail_path',
        'captions_path',
        'duration',
        'width',
        'height',
        'file_size',
        'mime_type',
        'status',
        'visibility',
        'view_count',
        'like_count',
        'comment_count',
        'share_count',
        'processing_ms',
        'stored_bytes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VideoStatus::class,
            'visibility' => VideoVisibility::class,
            'duration' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'share_count' => 'integer',
            'processing_ms' => 'integer',
            'stored_bytes' => 'integer',
            'published_at' => 'datetime',
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

    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    public function saves(): HasMany
    {
        return $this->hasMany(SavedVideo::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', VideoStatus::Published)
            ->where('visibility', VideoVisibility::Public);
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->relationLoaded('likes')
            ? $this->likes->contains('user_id', $user->id)
            : $this->likes()->where('user_id', $user->id)->exists();
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

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path ? Storage::disk($this->disk())->url($this->thumbnail_path) : null;
    }

    public function mediaUrl(): ?string
    {
        $path = $this->processed_path ?? $this->original_path;

        return $path ? Storage::disk($this->disk())->url($path) : null;
    }

    public function disk(): string
    {
        return (string) config('trevviq.video.disk', 'public');
    }
}
