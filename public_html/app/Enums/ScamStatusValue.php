<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum ScamStatusValue: string
{
    use EnumSupport;

    case REGISTERED = 'registered';
    // Add more status values as needed
}