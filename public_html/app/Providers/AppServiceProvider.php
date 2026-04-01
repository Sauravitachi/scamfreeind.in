<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('customer.auth', function ($app) {
            return new \App\Services\Customer\AuthService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\ScamRegistration::observe(\App\Observers\ScamRegistrationObserver::class);
        \App\Models\ScamLead::observe(\App\Observers\ScamLeadObserver::class);
    }
}
