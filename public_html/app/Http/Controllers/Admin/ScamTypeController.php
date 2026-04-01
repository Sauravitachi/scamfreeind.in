<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\ScamTypeRequest;
use App\Models\ScamType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamTypeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScamTypeController extends \App\Foundation\Controller
{
    /**
     * Constructor for ScamTypeController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected ScamTypeService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_TYPE_LIST, ['index']),
            permit(Permission::SCAM_TYPE_CREATE, ['create', 'store']),
            permit(Permission::SCAM_TYPE_UPDATE, ['edit', 'update']),
            permit(Permission::SCAM_TYPE_DELETE, ['destroy']),
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

        $this->activityLogService->visited('scam types list');

        return view('admin.scam-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create scam type');

        return view('admin.scam-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamTypeRequest $request): JsonResponse
    {
        $scamType = $this->service->create($request);

        $this->activityLogService->created('scam type', $scamType);

        $this->flashToast('success', 'Scam Type Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-types.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ScamType $scamType): View
    {
        $this->activityLogService->visited('scam type detail', $scamType);

        return view('admin.scam-types.show', compact('scamType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScamType $scamType): View
    {
        $this->activityLogService->visited('edit scam', $scamType);

        return view('admin.scam-types.edit', compact('scamType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamTypeRequest $request, ScamType $scamType): JsonResponse
    {
        try {

            if ($this->service->update($scamType, $request)) {
                $toast = ['type' => 'success', 'message' => 'Scam Type Updated!'];
                $this->activityLogService->updated('scam type', $scamType);
            } else {
                $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
            }

            return $this->responseService->json(success: true, toast: $toast);

        } catch (InvalidRequestException $e) {

            return $this->responseService->exceptionToast($e);

        } catch (Exception $e) {

            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScamType $scamType): JsonResponse
    {
        try {
            $this->service->delete($scamType);

            $this->activityLogService->deleted('scam type', $scamType);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Scam Type Deleted!']);

        } catch (InvalidRequestException $e) {

            return $this->responseService->exceptionToast($e);

        } catch (Exception $e) {

            throw $e;
        }

    }
}
