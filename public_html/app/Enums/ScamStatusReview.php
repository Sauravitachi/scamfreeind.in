<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum ScamStatusReview: string
{
    use EnumSupport;

    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
            self::APPROVED => 'Approved',
        };
    }
}
