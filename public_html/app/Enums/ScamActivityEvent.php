<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum ScamActivityEvent: string
{
    use EnumSupport;

    case CREATED = 'created';
    case UPDATED = 'updated';
    case SALES_ASSIGN = 'sales_assign';
    case DRAFTING_ASSIGN = 'drafting_assign';
    case SERVICE_ASSIGN = 'service_assign';
    case SALES_STATUS = 'sales_status';
    case DRAFTING_STATUS = 'drafting_status';
    case RECYCLED = 'recycled';
    case SUB_ADMIN_ASSIGN = 'sub_admin_assign';
}
