<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Actions\Scams\UnassignScamsWithStatus;
use App\Actions\CustomerEnquiry\UnassignEnquiriesWithStatus;

Schedule::call(function () {
    (new UnassignScamsWithStatus())->handle();
    (new UnassignEnquiriesWithStatus())->handle();
})->dailyAt('00:00');
