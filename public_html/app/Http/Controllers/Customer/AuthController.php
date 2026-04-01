<?php

namespace App\Http\Controllers\Customer;

use App\Facades\CustomerAuth;
use App\Http\Requests\Customer\Auth\ConfirmOtpRequest;
use App\Http\Requests\Customer\Auth\SendOtpRequest;
use App\Models\Otp;
use App\Services\Customer\AuthService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends \App\Foundation\Controller
{
    public function __construct(
        protected AuthService $service,
        protected ResponseService $responseService,
    ) {}

    public function login(): View
    {
        return view('customer.auth.login');
    }

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->service->sendOtp($request);

        return $this->responseService->json(success: true, message: 'OTP Sent!', data: [
            'request' => $request->validated(),
        ]);
    }

    public function confirmOtp(ConfirmOtpRequest $request): JsonResponse
    {
        $otpInput = $request->validated('otp');

        $otp = Otp::where('otp', $otpInput)
            ->whereNotNull('customer_id')
            ->whereNull('used_at')
            ->where('session_id', $request->session()->getId())
            ->where('expire_at', '>', now())->first(['id', 'customer_id']);

        if (! $otp) {
            return $this->responseService->errors(['otp' => ['Invalid OTP!']]);
        }

        $otp->update(['used_at' => now()]);

        CustomerAuth::login($otp->customer_id);
        $request->session()->regenerate();

        return $this->responseService->json(success: true, redirectTo: route('customer.home.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        CustomerAuth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
