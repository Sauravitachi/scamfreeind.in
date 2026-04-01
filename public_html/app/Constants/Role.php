<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum Role: string
{
    use EnumSupport;

    case SUPER_ADMIN = 'Super Admin';
    case SALES_MEMBER = 'Sales Member';
    case DRAFTING_MEMBER = 'Drafting Member';
    case SERVICE_MEMBER = 'Service Member';
}
