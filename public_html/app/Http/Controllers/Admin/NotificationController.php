<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends \App\Foundation\Controller
{
    /**
     * Mark the latest unread notification as read for the current user.
     */
    public function markLatestNotificationAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $latestNotification = $user->unreadNotifications()->latest()->first();
        if ($latestNotification) {
            $latestNotification->markAsRead();
        }
        return $this->responseService->json(success: true);
    }

    /**
     * Constructor for NotificationController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected NotificationService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit([Permission::NOTIFICATION_LIST, Permission::NOTIFICATION_LIST_SELF], only: ['index', 'show', 'unreadNotificationsCount']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable($request)->toJson();
        }

        $this->activityLogService->visited('notifications list');

        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return view('admin.notifications.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $notification = DatabaseNotification::find($id);

        return $this->responseService->json(success: true, data: $notification);
    }

    public function unreadNotificationsCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $latestNotification = $user->unreadNotifications()->latest()->first(['id', 'data']);

        $count = $user->unreadNotifications()->count();

        // updating user's last ping time
        $user->update(['last_pinged_at' => now()]);

        return $this->responseService->json(success: true, data: compact('count', 'latestNotification'));
    }

    public function unreadNotifications(Request $request): JsonResponse
    {
        $data = $this->service->getUserNotificationDropdownData($request);

        return $this->responseService->json(success: true, data: $data);
    }
}
