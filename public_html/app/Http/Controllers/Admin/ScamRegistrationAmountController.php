<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\DTO\Toast;
use App\Http\Requests\Admin\ScamRegistrationAmountRequest;
use App\Models\ScamRegistrationAmount;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamRegistrationAmountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScamRegistrationAmountController extends \App\Foundation\Controller
{
    /**
     * Constructor for ScamRegistrationAmountController
     */
    public function __construct(
        protected ScamRegistrationAmountService $service,
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_REGISTRATION_AMOUNT_LIST, only: ['index', 'show']),
            permit(Permission::SCAM_REGISTRATION_AMOUNT_CREATE, only: ['create', 'store']),
            permit(Permission::SCAM_REGISTRATION_AMOUNT_UPDATE, only: ['edit', 'update']),
            permit(Permission::SCAM_REGISTRATION_AMOUNT_DELETE, only: ['destroy']),
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

        $this->activityLogService->visited('scam registration amount list');

        return view('admin.scam-registration-amounts.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create scam registration amount');

        return view('admin.scam-registration-amounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamRegistrationAmountRequest $request): JsonResponse
    {
        $scamRegistrationAmount = $this->service->create($request);

        $this->flashToast(new Toast(type: 'success', message: 'Registration Amount Added!'));

        $this->activityLogService->created('scam registration amount', $scamRegistrationAmount);

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-registration-amounts.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ScamRegistrationAmount $scamRegistrationAmount): View
    {
        $this->activityLogService->visited('scam registration amount', $scamRegistrationAmount);

        return view('admin.scam-registration-amounts.show', compact('scamRegistrationAmount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScamRegistrationAmount $scamRegistrationAmount): View
    {
        $this->activityLogService->visited('edit scam registration amount', $scamRegistrationAmount);

        return view('admin.scam-registration-amounts.edit', compact('scamRegistrationAmount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamRegistrationAmountRequest $request, ScamRegistrationAmount $scamRegistrationAmount): JsonResponse
    {
        $updated = $this->service->update($scamRegistrationAmount, $request);

        if (! $updated) {
            return $this->responseService->json(success: true, toast: new Toast(type: 'warning', message: 'Nothing to update!'));
        }

        $this->flashToast(new Toast('success', 'Registration Amount Updated!'));

        $this->activityLogService->updated('scam registration amount', $scamRegistrationAmount);

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-registration-amounts.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScamRegistrationAmount $scamRegistrationAmount): JsonResponse
    {
        if (! $this->service->isDeletable($scamRegistrationAmount)) {
            return $this->responseService->json(success: false, toast: new Toast(type: 'warning', message: 'Cannot delete this registration price!'));
        }

        $this->service->delete($scamRegistrationAmount);

        $this->activityLogService->deleted('scam registration amount', $scamRegistrationAmount);

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message: 'Registration Amount Deleted!'));
    }

    /**
     * Chnages the status of the user
     */
    public function changeStatus(Request $request, ScamRegistrationAmount $scamRegistrationAmount): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);

        $scamRegistrationAmount->update(['is_active' => (bool) $request->input('is_active', 1)]);

        $this->activityLogService->updated('scam registration amount status', $scamRegistrationAmount);

        return $this->responseService->json(success: true, message: 'Status updated!');
    }
}
