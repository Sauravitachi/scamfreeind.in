<?php
return [
    // Default format for displaying date and time throughout the application
    'clean_datetime_format' => 'Y-m-d H:i:s',
    // Phone numbers to bypass from being recorded as scam leads
    'bypassed_phone_numbers' => explode(',', env('BYPASS_PHONE_NUMBERS', '9117442498,7889267713,7009485497,8533056030,9555613534,9501595324')),
];
