<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WatiWebhookMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the Auth-Key from the config
        $authKey = config('settings.wati_webhook_auth_key');

        // Check if the Auth-Key header is present and matches the config
        $incomingAuthKey = $request->header('Auth-Key') ?? $request->input('auth_key');

        if ($incomingAuthKey !== $authKey) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Proceed to the next middleware or request handler
        return $next($request);
    }
}
