<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Http\Requests\Admin\ScamSourceRequest;
use App\Models\ScamSource;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamSourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScamSourceController extends \App\Foundation\Controller
{
    /**
     * Constructor for ScamSourceController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ScamSourceService $service,
        protected ResponseService $responseService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_SOURCE_LIST, only: ['index', 'show']),
            permit(Permission::SCAM_SOURCE_CREATE, only: ['create', 'store']),
            permit(Permission::SCAM_SOURCE_UPDATE, only: ['edit', 'update']),
            permit(Permission::SCAM_SOURCE_DELETE, only: ['destroy']),
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

        $this->activityLogService->visited('scam source list');

        return view('admin.scam-sources.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->activityLogService->visited('create scam source');

        return view('admin.scam-sources.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamSourceRequest $request): JsonResponse
    {
        $scamSource = $this->service->create($request);

        $this->activityLogService->created('scam source', $scamSource);

        $this->flashToast('success', 'Scam Source Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-sources.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ScamSource $scamSource): View
    {
        $this->activityLogService->visited('scam source detail', $scamSource);

        return view('admin.scam-sources.show', compact('scamSource'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScamSource $scamSource): View
    {
        $this->activityLogService->visited('edit scam source', $scamSource);

        return view('admin.scam-sources.edit', compact('scamSource'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamSourceRequest $request, ScamSource $scamSource): JsonResponse
    {
        if ($this->service->update($scamSource, $request)) {
            $toast = ['type' => 'success', 'message' => 'Scam Source Updated!'];
            $this->activityLogService->updated('scam source', $scamSource);
        } else {
            $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScamSource $scamSource): JsonResponse
    {

        if ($this->service->isDeletable($scamSource)) {

            $this->service->delete($scamSource);

            $this->activityLogService->deleted('scam source', $scamSource);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Scam Source Deleted!']);

        }

        return $this->responseService->json(success: true, toast: ['type' => 'warning', 'message' => 'Scam Source is in usage!']);
    }

    /**
     * Select Search for scams
     */
    public function selectSearch(Request $request): JsonResponse
    {
        $results = $this->service->selectSearch($request);

        return $this->responseService->json(success: true, data: $results);
    }
}
