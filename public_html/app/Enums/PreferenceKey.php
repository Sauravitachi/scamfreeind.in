<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum PreferenceKey: string
{
    use EnumSupport;

    case THEME = 'theme';
    case MENU_LAYOUT = 'menu_layout';
    case SUB_ADMIN_ID = 'sub_admin_id';

    public function label(): string
    {
        return match ($this) {
            self::THEME => 'Theme',
            self::MENU_LAYOUT => 'Menu Layout',
            self::SUB_ADMIN_ID => 'Sub Admin ID',
        };
    }
}
