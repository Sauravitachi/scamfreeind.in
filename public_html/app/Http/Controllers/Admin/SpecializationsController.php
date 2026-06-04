<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\SpecializationRequest;
use App\Models\ProblemType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\SpecializationsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecializationsController extends \App\Foundation\Controller
{
    /**
     * Constructor for SpecializationsController.
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected SpecializationsService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods.
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SPECIALIZATION_LIST, only: ['index', 'show']),
            permit(Permission::SPECIALIZATION_CREATE, only: ['create', 'store']),
            permit(Permission::SPECIALIZATION_UPDATE, only: ['edit', 'update']),
            permit(Permission::SPECIALIZATION_DELETE, only: ['destroy']),
        ];
    }

    /**
     * Display a listing of specializations.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('specializations list');

        return view('admin.specializations.index');
    }

    /**
     * Show the form for creating a new specialization.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create specialization');

        return view('admin.specializations.create');
    }

    /**
     * Store a newly created specialization.
     */
    public function store(SpecializationRequest $request): JsonResponse
    {
        $specialization = $this->service->create($request);

        $this->activityLogService->created('specialization', $specialization);

        $this->flashToast('success', 'Specialization Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.specializations.index'));
    }

    /**
     * Display the specified specialization.
     */
    public function show(ProblemType $specialization): View
    {
        $this->activityLogService->visited('specialization detail', $specialization);

        return view('admin.specializations.show', compact('specialization'));
    }

    /**
     * Show the form for editing the specified specialization.
     */
    public function edit(ProblemType $specialization): View
    {
        $this->activityLogService->visited('edit specialization', $specialization);

        return view('admin.specializations.edit', compact('specialization'));
    }

    /**
     * Update the specified specialization.
     */
    public function update(SpecializationRequest $request, ProblemType $specialization): JsonResponse
    {
        try {
            if ($this->service->update($specialization, $request)) {
                $toast = ['type' => 'success', 'message' => 'Specialization Updated!'];
                $this->activityLogService->updated('specialization', $specialization);
            } else {
                $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
            }

            return $this->responseService->json(success: true, toast: $toast);
        } catch (InvalidRequestException $e) {
            return $this->responseService->exceptionToast($e);
        } catch (Exception $e) {
            return $this->responseService->json(success: false, toast: ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified specialization.
     */
    public function destroy(ProblemType $specialization): JsonResponse
    {
        try {
            $this->service->delete($specialization);
            $this->activityLogService->deleted('specialization', $specialization);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Specialization Deleted!']);
        } catch (InvalidRequestException $e) {
            return $this->responseService->exceptionToast($e);
        } catch (Exception $e) {
            return $this->responseService->json(success: false, toast: ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
