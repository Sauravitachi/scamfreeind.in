<?php
namespace App\Enums;

use App\Traits\EnumSupport;

enum ScamStatusType: string
{
    use EnumSupport;

    case DRAFTING = 'drafting';
    case SALES = 'sales';
    case SERVICE = 'service';
}
