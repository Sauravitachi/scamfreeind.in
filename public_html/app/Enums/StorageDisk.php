<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum StorageDisk: string
{
    use EnumSupport;

    case LOCAL = 'local';
    case PUBLIC = 'public';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::LOCAL => 'Local',
            self::PUBLIC => 'Public',
            self::S3 => 'S3'
        };
    }
}
