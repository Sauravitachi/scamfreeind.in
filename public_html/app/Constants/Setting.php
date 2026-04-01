<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum Setting: string
{
    use EnumSupport;

    case PANEL_LOGIN = 'panel_login';
    case IP_LOGIN = 'ip_login';
    case ALLOWED_IPS = 'allowed_ips';
}
