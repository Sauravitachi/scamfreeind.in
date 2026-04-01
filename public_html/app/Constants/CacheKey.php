<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum CacheKey: string
{
    use EnumSupport;

    case COUNTRY_CODES_ARRAY = 'cache:country_codes_array';
}
