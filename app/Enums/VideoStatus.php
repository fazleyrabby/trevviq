<?php

namespace App\Enums;

enum VideoStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Published = 'published';
    case Rejected = 'rejected';
    case Blocked = 'blocked';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Blocked => 'Blocked',
            self::Deleted => 'Deleted',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
