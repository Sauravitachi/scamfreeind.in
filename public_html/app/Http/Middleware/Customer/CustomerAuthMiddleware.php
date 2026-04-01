<?php

namespace App\Http\Middleware\Customer;

use App\Facades\CustomerAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! CustomerAuth::check()) {
            return redirect()->to(CustomerAuth::guestsRedirectUrl());
        }

        return $next($request);
    }
}
