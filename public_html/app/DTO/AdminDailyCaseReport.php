<?php

namespace App\DTO;

class AdminDailyCaseReport
{
    public function __construct(
        public readonly int $totalCases,
        public readonly array $sourceCases
    ) {}
}
