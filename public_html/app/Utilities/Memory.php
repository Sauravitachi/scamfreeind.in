<?php

namespace App\Utilities;

class Memory
{
    private static array $data = [];

    public static function remember(string $key, callable $cb)
    {
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        }

        return self::$data[$key] = $cb();
    }
}
