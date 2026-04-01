<?php

namespace App\Services\Customer;

use App\Http\Requests\Customer\Auth\SendOtpRequest;
use App\Jobs\Sms\SendOtpToCustomer;
use App\Models\Customer;
use App\Services\Service;
use App\Constants\Setting;

class AuthService extends Service
{
    public function sendOtp(SendOtpRequest $request)
    {
        if (setting(Setting::IP_LOGIN, false)) {
            $allowedIps = setting(Setting::ALLOWED_IPS, '');
            $allowedIps = array_filter(array_map('trim', explode(',', $allowedIps)));
            $currentIp = $request->ip();

            if (! in_array($currentIp, $allowedIps)) {
                abort(403, "IP Restricted! Your IP ($currentIp) is not allowed to request OTP.");
            }
        }

        $customer = Customer::wherePhoneDetails($request->phone_number, 'in')->first(['id']);
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        $sessionId = $request->session()->getId();
        SendOtpToCustomer::dispatchSync($customer->id, $sessionId, $userAgent, $ipAddress);
    }

    /**
     * Check if a customer is authenticated.
     */
    public function check(): bool
    {
        return session()->has('customer_id');
    }

    /**
     * Get the redirect URL for guests (not authenticated customers).
     */
    public function guestsRedirectUrl(): string
    {
        // Return the route to the customer login page
        return route('customer.login');
    }

    /**
     * Log in the customer by storing their ID in the session.
     */
    public function login($customerId): void
    {
        session(['customer_id' => $customerId]);
    }

    public function logout(): void
    {
        session()->forget('customer_id');
    }
}
