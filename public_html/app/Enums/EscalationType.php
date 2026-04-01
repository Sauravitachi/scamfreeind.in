<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum EscalationType: string
{
    use EnumSupport;

    case SALES = 'sales';
    case DRAFTING = 'drafting';
    case SERVICE = 'service';

    public function label(): string
    {
        return match ($this) {
            self::SALES => 'Sales',
            self::DRAFTING => 'Drafting',
            self::SERVICE => 'Service',
        };
    }
}
