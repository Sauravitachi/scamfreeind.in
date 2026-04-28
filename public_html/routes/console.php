<?php

use App\Actions\CustomerEnquiry\UnassignEnquiriesWithStatus;
use App\Actions\Scams\UnassignScamsWithStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::call(function () {
//     Log::info('every minute unassignment task.');

//     $scamsCount = (new UnassignScamsWithStatus())->handle();
//     $enquiriesCount = (new UnassignEnquiriesWithStatus())->handle();

//     Log::info("every minute unassignment task completed. Scams unassigned: {$scamsCount}, Enquiries unassigned: {$enquiriesCount}");
// })->everyMinute();
