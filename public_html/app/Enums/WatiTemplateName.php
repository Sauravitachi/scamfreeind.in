<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum WatiTemplateName: string
{
    use EnumSupport;

    case DRAFTING_SCAM_ASSIGN_NOTIFICATION = 'drafting_case_assign_notification';
    case SALES_CASE_ASSIGN_NOTIFICATION = 'sales_case_assign_notification';

    public function broadcastName(): string
    {
        return match ($this) {
            self::DRAFTING_SCAM_ASSIGN_NOTIFICATION => 'drafting_case_assign_notification',
            self::SALES_CASE_ASSIGN_NOTIFICATION => 'sales_case_assign_notification'
        };
    }
}
