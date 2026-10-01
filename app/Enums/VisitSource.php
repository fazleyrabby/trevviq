<?php

namespace App\Enums;

enum VisitSource: string
{
    case Claimed = 'claimed';
    case Auto = 'auto';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Claimed => 'Claimed by traveller',
            self::Auto => 'Automatic',
            self::Admin => 'Added by staff',
        };
    }
}
