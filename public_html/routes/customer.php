<?php

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Middleware\Customer\CustomerAuthMiddleware;
use App\Http\Middleware\Customer\CustomerGuestMiddleware;

$routes = function () {

    Route::controller(AuthController::class)->middleware([
        CustomerGuestMiddleware::class,
    ])->group(function () {

        Route::get('login', 'login')->name('login');
        Route::post('send-otp', 'sendOtp')->name('send-otp');
        Route::post('confirm-otp', 'confirmOtp')->name('confirm-otp');
        Route::post('logout', 'logout')->name('logout')->withoutMiddleware(CustomerGuestMiddleware::class);

    });

    Route::middleware([
        CustomerAuthMiddleware::class,
    ])->group(function () {
        Route::controller(HomeController::class)->as('home.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('raise-enquiry', 'raiseEnquiry')->name('raise-enquiry');
        });
    });

};

if (app()->environment('production')) {
    Route::domain('user.scamfreeindia.com')->name('customer.')->group($routes);
} else {
    Route::name('customer.')->group($routes);
}
