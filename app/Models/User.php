<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'username', 'email', 'password', 'bio', 'avatar_path', 'country_code', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Public URL for the user's avatar, or null when none has been uploaded.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null);
    }

    /**
     * One or two leading initials, used as an avatar fallback.
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = array_map(static fn (string $word): string => mb_substr($word, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $initials)) ?: '?';
    }

    public function isViewer(): bool
    {
        return $this->role === UserRole::Viewer;
    }

    public function isTraveller(): bool
    {
        return $this->role === UserRole::Traveller;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(TravellerLocation::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function savedVideos(): HasMany
    {
        return $this->hasMany(SavedVideo::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Travellers this user follows.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Travellers who follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function isFollowing(self $user): bool
    {
        return $this->following()->whereKey($user->getKey())->exists();
    }
}
