<?php

declare(strict_types=1);

namespace App\DTO;

use InvalidArgumentException;

/**
 * A Data Transfer Object for toast notifications.
 */
class Toast
{
    private const VALID_TYPES = ['success', 'warning', 'error'];

    /**
     * @param  string  $type  The type of the toast (success, warning, error).
     * @param  string  $message  The message for the toast.
     *
     * @throws InvalidArgumentException If the type is invalid.
     */
    public function __construct(
        public string $type,
        public string $message
    ) {
        $this->validate();
    }

    /**
     * Returns the toast data as an associative array.
     *
     * @return array The toast data.
     */
    public function data(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
        ];
    }

    /**
     * Validates the toast type.
     *
     * @param  string  $type
     *
     * @throws InvalidArgumentException If the type is invalid.
     */
    private function validate(): void
    {
        throw_if(
            condition: ! in_array($this->type, self::VALID_TYPES, true),
            exception: InvalidArgumentException::class,
            parameters: 'Toast type is invalid!'
        );
    }
}
