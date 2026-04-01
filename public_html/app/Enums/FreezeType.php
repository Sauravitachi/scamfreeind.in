<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum FreezeType: string
{
    use EnumSupport;

    case SCAMS = 'scams';
    case ENQUIRY = 'enquiry';

}
