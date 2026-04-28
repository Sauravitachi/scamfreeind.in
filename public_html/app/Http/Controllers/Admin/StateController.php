<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Http\Requests\Admin\StateRequest;
use App\Models\State;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\StateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StateController extends \App\Foundation\Controller
{
    /**
     * Constructor for StateController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected StateService $service,
        protected ResponseService $responseService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::STATE_LIST, only: ['index', 'show']),
            permit(Permission::STATE_CREATE, only: ['create', 'store']),
            permit(Permission::STATE_UPDATE, only: ['edit', 'update']),
            permit(Permission::STATE_DELETE, only: ['destroy']),
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

        $this->activityLogService->visited('state list');

        return view('admin.states.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->activityLogService->visited('create state');

        return view('admin.states.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StateRequest $request): JsonResponse
    {
        $state = $this->service->create($request);

        $this->activityLogService->created('state', $state);

        $this->flashToast('success', 'State Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.states.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(State $state): View
    {
        $this->activityLogService->visited('state detail', $state);

        return view('admin.states.show', compact('state'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(State $state): View
    {
        $this->activityLogService->visited('edit state', $state);

        return view('admin.states.edit', compact('state'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StateRequest $request, State $state): JsonResponse
    {
        if ($this->service->update($state, $request)) {
            $toast = ['type' => 'success', 'message' => 'State Updated!'];
            $this->activityLogService->updated('state', $state);
        } else {
            $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(State $state): JsonResponse
    {
        if ($this->service->isDeletable($state)) {
            $this->service->delete($state);
            $this->activityLogService->deleted('state', $state);

            return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'State Deleted!']);
        }

        return $this->responseService->json(success: true, toast: ['type' => 'warning', 'message' => 'State is in usage!']);
    }

    /**
     * Select Search for states
     */
    public function selectSearch(Request $request): JsonResponse
    {
        $results = $this->service->selectSearch($request);

        return $this->responseService->json(success: true, data: $results);
    }
}
