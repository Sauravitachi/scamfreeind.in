<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum AppMode: string
{
    use EnumSupport;

    case LOCAL = 'local';
    case BETA = 'beta';
    case PRODUCTION = 'production';

    public function color(): string
    {
        return match ($this) {
            self::LOCAL => '#6B7280',
            self::BETA => '#F97316',
            self::PRODUCTION => '#10B981',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::BETA => 'Beta',
            self::PRODUCTION => 'Production',
        };
    }
}
