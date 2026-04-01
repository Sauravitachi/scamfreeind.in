<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait EnumSupport
{
    /**
     * Check if the enum contains a given value.
     */
    public static function contains(string|int $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }

    /**
     * Return an array of enum values if it's a backed enum,
     * otherwise return an array of enum names.
     */
    public static function array(): array
    {
        $cases = self::cases();

        return array_column($cases, isset($cases[0]->value) ? 'value' : 'name');
    }

    /**
     * Return a collection of enum values if it's a backed enum,
     * otherwise return a collection of enum names.
     */
    public static function all(): Collection
    {
        return collect(self::array());
    }

    /**
     * Return an associative array of enum values and their corresponding labels.
     *
     * @return array An associative array of enum values as keys and labels as values.
     */
    public static function selectArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(function ($case) {
                return [$case->value => $case->label()];
            })
            ->toArray();
    }

    /**
     * Return the label of the enum value.
     *
     * @return string The label of the enum value, or an empty string if the value is not a string.
     */
    public function label(): string
    {
        if (is_string($this->value)) {
            return ucwords($this->value);
        }

        return '';
    }

    /**
     * Return the number of enum cases.
     */
    public static function count(): int
    {
        return count(self::cases());
    }
}
