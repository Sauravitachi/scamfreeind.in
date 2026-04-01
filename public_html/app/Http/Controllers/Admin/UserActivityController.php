<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission as PermissionConstant;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\UserActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class UserActivityController extends \App\Foundation\Controller implements HasMiddleware
{
    /**
     * Constructor for UserActivityController
     */
    public function __construct(
        protected UserActivityService $service,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit([PermissionConstant::VIEW_ALL_USERS_ACTIVITIES, PermissionConstant::VIEW_SELF_USERS_ACTIVITIES], only: ['index']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('user activity page');

        $users = User::all(['id', 'name', 'username'])->append('name_with_username');

        return view('admin.user-activities.index', compact('users'));
    }
}
