<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum Status: string
{
    use EnumSupport;

    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case VERIFIED = 'verified';
    case SUCCESS = 'success';

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#ffc400',
            self::REJECTED => '#ff4436',
            self::APPROVED, self::VERIFIED, self::SUCCESS => '#6BC167',
        };
    }

    public function fadedColor(): string
    {
        return match ($this) {
            self::PENDING => '#ffebb6',
            self::REJECTED => '#ffd5d1',
            self::APPROVED, self::VERIFIED, self::SUCCESS => '#b4eda0',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::REJECTED => 'Rejected',
            self::APPROVED => 'Approved',
            self::VERIFIED => 'Verified',
            self::SUCCESS => 'Success',
        };
    }
}
