<?php

namespace App\Utilities;

use App\DTO\SplittedName;

class Structure
{
    public static function notificationData(string $title, ?string $message = null, ?string $link = null): array
    {
        return array_filter(get_defined_vars(), fn ($val) => $val !== null);
    }

    public static function splitFullName(string $fullName): SplittedName
    {
        $firstName = null;
        $lastName = null;

        if (! empty(trim($fullName))) {
            $name = trim($fullName);
            $nameParts = explode(' ', $name);
            if (count($nameParts) === 1) {
                $firstName = $nameParts[0];
                $lastName = null;
            } else {
                $firstName = $nameParts[0];
                $lastName = implode(' ', array_slice($nameParts, 1));
            }
        }

        return new SplittedName($firstName, $lastName);
    }
}
