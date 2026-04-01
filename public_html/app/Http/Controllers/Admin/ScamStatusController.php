<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ScamStatus\StoreTransition;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Enums\ScamStatusType;
use App\Exceptions\CycleDetectedException;
use App\Http\Requests\Admin\ScamStatusRequest;
use App\Models\Role;
use App\Models\ScamStatus;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScamStatusController extends \App\Foundation\Controller
{
    /**
     * Constructor for ScamStatusController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ScamStatusService $service,
        protected ResponseService $responseService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_STATUS_LIST, only: ['index', 'show']),
            permit(Permission::SCAM_STATUS_CREATE, only: ['create', 'store']),
            permit(Permission::SCAM_STATUS_UPDATE, only: ['edit', 'update']),
            permit(Permission::SCAM_STATUS_DELETE, only: ['destroy']),
            permit(Permission::SCAM_STATUS_TRANSITION_SHOW, ['transition']),
            permit(Permission::SCAM_STATUS_TRANSITION_UPDATE, ['handleTransition']),

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
        $this->activityLogService->visited('scam status list');

        return view('admin.scam-statuses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create scam status');
        $scamStatusTypes = ScamStatusType::cases();
        $roles = Role::all(['id', 'name']);

        return view('admin.scam-statuses.create', compact('scamStatusTypes', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamStatusRequest $request): JsonResponse
    {
        $scamStatus = $this->service->create($request);
        $this->activityLogService->created('scam status', $scamStatus);
        $this->flashToast('success', 'Scam Status Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-statuses.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ScamStatus $scamStatus): View
    {
        $this->activityLogService->visited('scam status detail', $scamStatus);

        return view('admin.scam-statuses.show', compact('scamStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScamStatus $scamStatus): View
    {
        $roles = Role::all(['id', 'name']);
        $this->activityLogService->visited('edit scam status', $scamStatus);
        $scamStatusTypes = ScamStatusType::cases();

        return view('admin.scam-statuses.edit', compact('scamStatus', 'scamStatusTypes', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamStatusRequest $request, ScamStatus $scamStatus): JsonResponse
    {
        $this->service->update($scamStatus, $request);
        $toast = ['type' => 'success', 'message' => 'Scam Status Updated!'];
        $this->activityLogService->updated('scam status', $scamStatus);

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScamStatus $scamStatus): JsonResponse
    {
        $this->service->delete($scamStatus);
        $this->activityLogService->deleted('scam status', $scamStatus);

        return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Scam Status Deleted!']);
    }

    public function transition(ScamStatusType $type): View
    {
        abort_if($type !== ScamStatusType::DRAFTING, 404); // only enabling for drafting statuses
        $statuses = ScamStatus::with('nextStatuses')->where('type', $type)->orderBy('index')->get();

        return view('admin.scam-statuses.status-transition', compact('type', 'statuses'));
    }

    public function handleTransition(Request $request, StoreTransition $storeTransition): JsonResponse
    {
        try {
            $storeTransition->handle($request);
        } catch (CycleDetectedException $e) {
            return $this->responseService->json(success: false, toast: new Toast('error', $e->getMessage()));
        }

        return $this->responseService->json(success: true, toast: new Toast('success', 'Transition saved!'));
    }
}
