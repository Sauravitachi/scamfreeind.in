<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Http\Requests\Admin\LawyerRequest;
use App\Models\Lawyer;
use App\Models\ProblemType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\LawyersService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LawyersController extends \App\Foundation\Controller
{
    /**
     * Constructor for LawyersController.
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected LawyersService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods.
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::LAWYER_LIST, only: ['index', 'show']),
            permit(Permission::LAWYER_CREATE, only: ['create', 'store']),
            permit(Permission::LAWYER_UPDATE, only: ['edit', 'update']),
            permit(Permission::LAWYER_DELETE, only: ['destroy']),
        ];
    }

    /**
     * Display a listing of lawyers.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('lawyers list');

        return view('admin.lawyers.index');
    }

    /**
     * Show the form for creating a new lawyer.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create lawyer');
        $specializations = ProblemType::orderBy('title')->get();

        return view('admin.lawyers.create', compact('specializations'));
    }

    /**
     * Store a newly created lawyer.
     */
    public function store(LawyerRequest $request): JsonResponse
    {
        $lawyer = $this->service->create($request);

        $this->activityLogService->created('lawyer', $lawyer);

        $this->flashToast('success', 'Lawyer Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.lawyers.index'));
    }

    /**
     * Display the specified lawyer.
     */
    public function show(Lawyer $lawyer): View
    {
        $this->activityLogService->visited('lawyer detail', $lawyer);
        $lawyer->load('specializations');

        return view('admin.lawyers.show', compact('lawyer'));
    }

    /**
     * Show the form for editing the specified lawyer.
     */
    public function edit(Lawyer $lawyer): View
    {
        $this->activityLogService->visited('edit lawyer', $lawyer);
        $lawyer->load('specializations');
        $specializations = ProblemType::orderBy('title')->get();

        return view('admin.lawyers.edit', compact('lawyer', 'specializations'));
    }

    /**
     * Update the specified lawyer.
     */
    public function update(LawyerRequest $request, Lawyer $lawyer): JsonResponse
    {
        try {
            if ($this->service->update($lawyer, $request)) {
                $toast = ['type' => 'success', 'message' => 'Lawyer Updated!'];
                $this->activityLogService->updated('lawyer', $lawyer);
            } else {
                $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
            }

            return $this->responseService->json(success: true, toast: $toast);
        } catch (Exception $e) {
            return $this->responseService->json(success: false, toast: ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified lawyer.
     */
    public function destroy(Lawyer $lawyer): JsonResponse
    {
        try {
            $this->service->delete($lawyer);
            $this->activityLogService->deleted('lawyer', $lawyer);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Lawyer Deleted!']);
        } catch (Exception $e) {
            return $this->responseService->json(success: false, toast: ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
