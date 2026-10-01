<?php

namespace App\Enums;

enum UserRole: string
{
    case Viewer = 'viewer';
    case Traveller = 'traveller';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Viewer => 'Viewer',
            self::Traveller => 'Traveller',
            self::Business => 'Business',
        };
    }
}
