<?php
require_once __DIR__ . '/common.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__));

$app->withRouting(
    web: [
        __DIR__.'/../routes/web.php',
        __DIR__.'/../routes/admin.php',
        __DIR__.'/../routes/customer.php',
    ],
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
);

$app->withMiddleware(function (Middleware $middleware) {

    /**
     * Default applied middlewares
     */
    $middleware->append([
        \App\Http\Middleware\DisableBrowserCacheStore::class,
    ]);

    /**
     * Aliases for middlewares
     */
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);

    /**
     * Skip csrf protection for these routes
     */
    $middleware->validateCsrfTokens(except: [
        'webhook/*',
    ]);

    /**
     * User/Guest redirect route setup
     */
    $middleware->redirectTo(
        guests: fn () => route('admin.auth.login'),
        users: fn () => route('admin.home')
    );
});

$app->withExceptions(function (Exceptions $exceptions): void {
    //
});

return $app->create();
