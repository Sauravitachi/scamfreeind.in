<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class CustomerAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'customer.auth';
    }
}
