<?php

namespace App\Enums;

enum VisitVerification: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::Pending => 'Pending',
            self::Verified => 'Verified',
        };
    }
}
