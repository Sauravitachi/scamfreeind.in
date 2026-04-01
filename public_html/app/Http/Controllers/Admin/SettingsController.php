<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\DTO\Toast;
use App\Http\Requests\Admin\BusinessSettingsRequest;
use App\Models\Role;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends \App\Foundation\Controller
{
    /**
     * Constructor for SettingsController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected SettingService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::LOGIN_SETTINGS, only: ['login']),
            permit(Permission::BUSINESS_SETTINGS, only: ['business', 'updateBusinessSetting']),
        ];
    }

    public function login(Request $request): JsonResponse|View
    {

        if (is_request_post($request)) {

            if ($this->service->updateLoginSetting($request)) {
                return $this->responseService->json(success: true);
            }

            return $this->responseService->json(success: true);

        }

        $settings = Setting::where('tag', 'login')->get(['key', 'value'])->keyBy('key');

        $this->activityLogService->visited('login settings');

        return view('admin.settings.login', compact('settings'));
    }

    public function business(): View
    {
        $settings = Setting::where('tag', 'business')->get(['key', 'value'])->keyBy('key');
        $roles = Role::all(['id', 'name', 'is_admin']);

        return view('admin.settings.business', compact('settings', 'roles'));
    }

    public function updateBusinessSetting(BusinessSettingsRequest $request)
    {
        $this->service->updateBusinessSetting($request);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Settings Updated!'));
    }
}
