<?php

namespace App\Enums;

use App\Traits\EnumSupport;

enum ContentType: string
{
    use EnumSupport;

    case HTML = 'text/html; charset=UTF-8';
    case JSON = 'application/json';
    case CSS = 'text/css; charset=UTF-8';
    case JS = 'application/javascript';
}
