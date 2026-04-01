<?php

namespace App\DTO;

use Carbon\Carbon;

class DatetimeRange
{
    public function __construct(
        public ?Carbon $start = null,
        public ?Carbon $end = null
    ) {}

    public function array(): array
    {
        return [$this->start, $this->end];
    }
}
