<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum EscalationStatus: string
{
    use EnumSupport;

    case OPEN = 'open';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => '#28a745',   // Green
            self::CLOSED => '#dc3545', // Red (for example)
        };
    }
}
