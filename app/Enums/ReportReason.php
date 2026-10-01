<?php

namespace App\Enums;

enum ReportReason: string
{
    case Spam = 'spam';
    case Offensive = 'offensive';
    case Misinformation = 'misinformation';
    case Copyright = 'copyright';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam or advertising',
            self::Offensive => 'Offensive or abusive',
            self::Misinformation => 'Misinformation',
            self::Copyright => 'Copyright infringement',
            self::Other => 'Something else',
        };
    }
}
