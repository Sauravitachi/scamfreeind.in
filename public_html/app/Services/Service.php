<?php

namespace App\Services;

abstract class Service
{
    private static array $instances = [];

    public static function getInstance(): static
    {
        return self::$instances[static::class] ??= new static;
    }
}
