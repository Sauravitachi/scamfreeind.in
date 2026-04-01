<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum MissedAutoScamAssignResolutionStatus: string
{
    use EnumSupport;

    case PENDING = 'pending';
    case DISMISSED = 'dismissed';
    case ASSIGNED = 'assigned';
}
