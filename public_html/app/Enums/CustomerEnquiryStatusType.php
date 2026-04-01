<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum CustomerEnquiryStatusType: string
{
    use EnumSupport;

    case DRAFTING = 'drafting';
    case SALES = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::DRAFTING => 'Drafting',
            self::SALES => 'Sales'
        };
    }
}
