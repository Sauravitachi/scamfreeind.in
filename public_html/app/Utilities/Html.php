<?php

namespace App\Utilities;

class Html
{
    public static function div(string $classname): string
    {
        return "<div class=\"$classname\"></div>";
    }

    public static function icon(string $classname): string
    {
        return "<i class=\"$classname\"></i>";
    }

    public static function selectCountriesData(): string
    {
        return cache()->remember('select_countries_data', 10 * 24 * 60 * 60, function (): string {
            return json_encode(
                array_map(
                    fn (array $country): array => [
                        'name' => $country['name'],
                        'calling_code' => $country['calling_code'],
                        'emoji' => $country['emoji'],
                    ],
                    countries(),
                ),
            );
        });
    }
}
