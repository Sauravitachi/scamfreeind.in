<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ActivityEvent;
use App\Constants\Permission as PermissionConstant;
use App\DTO\Toast;
use App\Http\Requests\Admin\ChangeAccountPasswordRequest;
use App\Http\Requests\Admin\UserForceReleaseFreezeRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Models\AutoScamAssignRecord;
use App\Models\Role;
use App\Models\Scam;
use App\Models\User;
use App\Models\UserScamStatusFreeze;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends \App\Foundation\Controller implements HasMiddleware
{
    /**
     * Constructor for UserController
     */
    public function __construct(
        protected UserService $service,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(PermissionConstant::USER_LIST, only: ['index']),
            permit(PermissionConstant::USER_CREATE, only: ['create', 'store']),
            permit(PermissionConstant::USER_DELETE, only: ['destroy']),
            permit(PermissionConstant::LOGIN_AS_USER, only: ['loginAsUser']),
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

        $this->activityLogService->visited('users');

        $roles = Role::orderBy('name', 'asc')->get(['id', 'name']);
        $subAdminUsers = User::role('Sub Admin')->orderBy('name')->get(['id', 'name']);
        return view('admin.users.index', compact('roles', 'subAdminUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->activityLogService->visited('create user');

        $user = $request->user();

        $roles = Role::whereIn(
            'id',
            json_decode($user->roles->first()?->user_creatable_roles ?? '[]')
        )->get(['id', 'name']);

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->service->create($request);

        $this->activityLogService->created('user', $user);

        $this->flashToast('success', 'User Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.users.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $userRole = $user->getRoleString();

        $user->load([
            'activities' => function ($q) {
                $q->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->limit(10);
            },
            'activities.causer:id,name,username',
        ]);

        if (in_array($userRole, ['sales', 'drafting'])) {

            $user->load([
                'scamStatusFreezes',
                'scamStatusFreezes.status:id,title,hours_to_freeze,freeze_scams_threshold,freeze_release_scams_threshold',
            ]);

            $user->scamStatusFreezes->each(function (UserScamStatusFreeze $freeze) use ($user, $userRole) {
                $scamCount = Scam::where("{$userRole}_assignee_id", $user->id)
                    ->where("{$userRole}_status_id", $freeze->status?->id)
                    ->count();
                $freeze->setAttribute('scam_count', $scamCount);
            });

        }

        $freezeSalesNullReleaseThreshold = setting('freeze_sales_null_release_threshold', null);

        $data = compact('user', 'freezeSalesNullReleaseThreshold');

        if ($request->ajax()) {

            $html = view(
                view: 'admin.users.ajax.user_details',
                data: $data
            )->render();

            return $this->responseService->json(success: true, html: $html);
        }

        return $this->responseService->json(success: true, data: $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->activityLogService->visited('update user');

        $user->load('roles');

        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $update = $this->service->update($user, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: ['type' => 'warning', 'message' => 'No Changes Made!']);
        }

        $this->activityLogService->updated('user', $user);

        $this->flashToast('success', 'User Details Updated!');

        return $this->responseService->json(success: true, redirectTo: route('admin.users.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($this->service->isDeletable($user) && $this->service->delete($user)) {
            $toast = ['type' => 'success', 'message' => 'User deleted!'];
            $this->activityLogService->deleted('user', $user);
        } else {
            $toast = ['type' => 'warning', 'message' => 'User can\'t be removed.'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Chnages the status of the user
     */
    public function changeStatus(Request $request, User $user): JsonResponse
    {
        $request->validate(['status' => 'required|boolean']);

        $user->update(['status' => (bool) $request->input('status', 1)]);

        $this->activityLogService->updated('user status', $user);

        return $this->responseService->json(success: true, message: 'Status updated!');
    }

    public function changePassword(ChangeAccountPasswordRequest $request, User $user): JsonResponse
    {
        $this->service->changePassword($request, $user);

        $this->activityLogService->updated('user password', $user);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Password updated!'));
    }

    public function forceReleaseFreeze(UserForceReleaseFreezeRequest $request, User $user): JsonResponse
    {
        $this->service->forceReleaseFreeze($user, $request);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Freeze has been released!'));
    }

    public function loginAsUser(Request $request, User $user): JsonResponse
    {
        // if already logged in as other user... cannnot do it again
        abort_if(session()->has('user_login'), 400, 'Cannot Login, already logged in as other user.');
        // if trying to log in own user account
        abort_if(Auth::id() === $user->id, 400, 'Cannot login your own account.');
        $this->activityLogService->log(description: 'Logged in as other user', event: ActivityEvent::LOGIN_AS_USER, properties: ['user_id' => $user->id]);
        session()->put('user_login', [
            'original_user' => Auth::user(),
            'head_back_url' => url()->previous(),
        ]);
        Auth::login($user);
        $this->flashToast('success', "You have been logged in as {$user->name} account!");

        return $this->responseService->json(success: true, redirectTo: route('admin.home'));
    }

    public function loginBackToUser(): RedirectResponse
    {
        abort_if(
            ! session()->has('user_login') ||
            ! ($originalUser = session('user_login')['original_user'] ?? null),
            400
        );

        Auth::login($originalUser);
        $userLoginSession = session()->get('user_login');
        session()->forget('user_login');
        $this->flashToast('success', 'Your account has been logged in!');
        if (isset($userLoginSession['head_back_url'])) {
            return redirect()->to($userLoginSession['head_back_url']);
        }

        return redirect()->route('admin.home');
    }

    public function assigneeStatus(Request $request, User $user): JsonResponse
    {
        $userType = $user->userType();

        $alerts = collect();

        if ($userType === 'sales') {
            $assingCount = AutoScamAssignRecord::where('assignee_type', 'sales')->where('user_id', $user->id)->whereDate('created_at', today())->count();
            if ($assingCount > 0) {
                $alerts->push([
                    'important' => true,
                    'icon' => 'ti ti-alert-hexagon',
                    'variant' => 'warning',
                    'message' => "Sales Member - {$user->nameWithUsername} has already been rewarded with auto assign {$assingCount} cases today.",
                ]);
            }
        }

        return $this->responseService->json(
            success: true,
            data: [
                'alerts' => $alerts,
                'user_type' => $userType,
            ]
        );
    }

    public function updatePreference(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        $user->preferences()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $this->activityLogService->updated("user preference $key", $user);

        return $this->responseService->json(success: true, message: 'Preference updated!');
    }

    public function setTarget(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'target_amount' => 'required|numeric|min:0',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'period_type' => 'required|string|in:monthly,weekly,daily',
        ]);

        $user->salesTargets()->updateOrCreate(
            [
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
            ],
            $data
        );

        $this->activityLogService->updated("user sales target", $user);

        return $this->responseService->json(success: true, message: 'Sales target updated!');
    }
}
