<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManychatWebhookMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Retrieve the Auth-Key from the config
        $authKey = config('settings.manychat_webhook_auth_key');

        // Check if the Auth-Key header is present and matches the config
        if ($request->header('Auth-Key') !== $authKey) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Proceed to the next middleware or request handler
        return $next($request);
    }
}
