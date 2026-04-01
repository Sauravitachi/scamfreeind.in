<?php

namespace App\Utilities;

use Illuminate\Validation\Rule;

class Validation
{
    /**
     * Get the validation rules for a valid country.
     *
     * @return array Validation rules for the country input.
     */
    public static function countryValidationRules(): string
    {
        return cache()->remember(
            'countries_key_string',
            30 * 86400,
            fn (): string => Rule::in(array_keys(countries()))->__toString()
        );
    }
}
