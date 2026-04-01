<?php

namespace App\DTO;

class ScamLeadTransferValidator
{
    /**
     * Constructor for ScamLeadTransferValidator
     *
     * @param  bool  $isSafe
     */
    public function __construct(
        private array $errors = [],
    ) {}

    public function push(string $errorMessage): void
    {
        $this->errors[] = $errorMessage;
    }

    public function isSafe(): bool
    {
        return count($this->errors) <= 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
