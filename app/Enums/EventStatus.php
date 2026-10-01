<?php

namespace App\Enums;

enum EventStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Published => 'Published',
            self::Cancelled => 'Cancelled',
            self::Rejected => 'Rejected',
            self::Blocked => 'Blocked',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
