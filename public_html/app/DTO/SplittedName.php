<?php

namespace App\DTO;

class SplittedName
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null
    ) {}
}
