<?php

namespace App\DTO;

class LoginPermit
{
    public function __construct(
        public bool $canLogin = false,
        public ?string $message = null
    ) {}
}
