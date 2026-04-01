<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission as PermissionConstant;
use App\Http\Requests\Admin\PermissionRequest;
use App\Models\Permission;
use App\Services\ActivityLogService;
use App\Services\PermissionService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PermissionController extends \App\Foundation\Controller implements HasMiddleware
{
    /**
     * Constructor for PermissionController
     */
    public function __construct(
        protected PermissionService $service,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(PermissionConstant::PERMISSION_LIST, only: ['index', 'show']),
            permit(PermissionConstant::PERMISSION_UPDATE, only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('permissions list');

        return view('admin.permissions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $this->activityLogService->visited('permission detail', $permission);

        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $this->activityLogService->visited('edit permission', $permission);

        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermissionRequest $request, Permission $permission)
    {
        if ($this->service->update($permission, $request)) {
            $toast = ['type' => 'success', 'message' => 'Permission Updated!'];
            $this->activityLogService->updated('permission', $permission);
        } else {
            $toast = ['type' => 'warning', 'message' => 'No Changes Made!'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }
}
