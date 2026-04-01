<?php

namespace App\Constants;

use App\Traits\EnumSupport;

enum HttpMethod: string
{
    use EnumSupport;

    case POST = 'POST';
    case GET = 'GET';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}
