<?php

namespace App\Http\Middleware;

use App\Constants\ActivityEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    protected int $timeout = 7200; // 2 hours in seconds

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {

            $user = $request->user();

            if (! session()->has('user_login') && ! $user->status) {
                Auth::logout();

                return redirect()->route('admin.auth.login');
            }

            $this->pingUser($user);

            if ($this->checkInactivity($request)) {
                $minutes = $this->timeout / 60;
                activity()->event(ActivityEvent::LOGOUT->value)
                    ->log(":causer.name_with_username logged out for being inactive for $minutes minute(s).");

                Auth::logout();

                return redirect()->route('admin.auth.login');
            }
        }

        return $next($request);
    }

    /**
     * Update the user's last pinged timestamp in cache.
     */
    protected function pingUser($user): void
    {
        Cache::remember("user-ping:{$user->id}", 10, fn () => $user->update(['last_pinged_at' => now()]));
    }

    /**
     * Check if the user has been inactive for longer than timeout.
     *
     * @return bool True if user is inactive and should be logged out.
     */
    protected function checkInactivity(Request $request): bool
    {
        $lastActivity = session('admin_access:last_activity_time');

        if ($lastActivity !== null && (time() - $lastActivity) > $this->timeout) {
            return true;
        }

        if (! request()->is('admin/notifications/unread-notifications-count')) {
            session(['admin_access:last_activity_time' => time()]);
        }

        return false;
    }
}
