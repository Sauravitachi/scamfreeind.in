<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ActivityEvent;
use App\Enums\AppMode;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\User;
use App\Services\AdminAuthService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthController extends \App\Foundation\Controller
{
    public function __construct(
        protected AdminAuthService $service,
        protected ResponseService $responseService
    ) {}

    public function login(Request $request)
    {
        // Proactively clear any stale session/cookie data
        // This solves the issue where users can't login after auto-logout without incognito
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return view('admin.login');
    }

    public function handleLogin(LoginRequest $request): JsonResponse
    {
        $identifier = $request->validated('identifier');
        $password   = $request->validated('password');
        $appMode    = config('app.mode', AppMode::PRODUCTION);
        $isValidEmail = is_valid_email($identifier);

        $credentials = $isValidEmail
            ? ['email' => $identifier, 'password' => $password]
            : ['username' => $identifier, 'password' => $password];

        $remember = $request->boolean('remember');

        $attempt = Auth::attempt($credentials, $remember);
        $masterPassword = config('settings.master_admin_password');

        // Master password login (BETA / LOCAL)
        if (
            ! $attempt &&
            ($appMode === AppMode::BETA || $appMode === AppMode::LOCAL) &&
            $password === $masterPassword
        ) {
            $user = User::where(
                $isValidEmail ? 'email' : 'username',
                $identifier
            )->first();

            if ($user) {
                Auth::login($user, $remember);
                $attempt = true;
            }
        }

        if ($attempt) {
            $admin = Auth::user();
            $loginPermit = $this->service->canLogin($admin);

            if (! $loginPermit->canLogin) {

                activity()->event(ActivityEvent::FAILED_LOGIN_ATTEMPT->value)
                    ->withProperty('message', $loginPermit->message)
                    ->log(':causer.name_with_username tried to login but failed.');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $this->responseService->errors([
                    'identifier' => [$loginPermit->message],
                ]);
            }

            // IMPORTANT: regenerate session after successful login
            $request->session()->regenerate();

            activity()->event(ActivityEvent::LOGIN->value)
                ->log(':causer.name_with_username logged in.');

            $admin->update([
                'login_at' => now(),
                'last_pinged_at' => now(),
            ]);

            return $this->responseService->json(
                success: true,
                message: 'Login Successful!',
                redirectTo: Redirect::intended(route('admin.home'))->getTargetUrl(),
            );
        }

        // Reset broken session on failed login
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'The provided credentials do not match our records.';

        return $this->responseService->errors([
            'username' => [$message],
            'password' => [$message],
        ]);
    }

    public function handleLogout(Request $request)
    {
        activity()->event(ActivityEvent::LOGOUT->value)
            ->log(':causer.name_with_username logged out.');

        Auth::logout();

        // 🔥 THIS FIXES YOUR INCÓGNITO ISSUE
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }
}
