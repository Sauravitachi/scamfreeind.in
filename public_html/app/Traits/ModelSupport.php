<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelSupport
{
    /**
     * Scope a query to filter by the primary key.
     *
     * @param  Builder  $query  The query builder instance.
     * @param  int|string  $id  The ID value to filter by.
     */
    public static function scopeWhereId(Builder $query, int|string $id): void
    {
        $query->where('id', $id);
    }

    /**
     * Scope a query to only include records created today.
     *
     * @param  Builder  $query  The query builder instance.
     */
    public static function scopeWhereToday(Builder $query): void
    {
        $query->whereDate('created_at', today());
    }

    /**
     * Check if the model has all of the given attributes.
     *
     * @param  array<string>|string  ...$attributes
     */
    public function hasAllAttributes(...$attributes): bool
    {
        // If the first argument is an array, use it as the attributes list
        $attributes = is_array($attributes[0]) ? $attributes[0] : $attributes;

        foreach ($attributes as $attribute) {
            if (! array_key_exists($attribute, $this->attributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the model has any of the given attributes.
     *
     * @param  array<string>|string  ...$attributes
     */
    public function hasAnyAttribute(...$attributes): bool
    {
        // If the first argument is an array, use it as the attributes list
        $attributes = is_array($attributes[0]) ? $attributes[0] : $attributes;

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $this->attributes)) {
                return true;
            }
        }

        return false;
    }
}
