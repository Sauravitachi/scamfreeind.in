<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission as PermissionConstant;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class RoleController extends \App\Foundation\Controller implements HasMiddleware
{
    /**
     * Constructor for RoleController
     */
    public function __construct(
        protected RoleService $service,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(PermissionConstant::ROLE_LIST, only: ['index', 'show']),
            permit(PermissionConstant::ROLE_CREATE, only: ['create', 'store']),
            permit(PermissionConstant::ROLE_UPDATE, only: ['edit', 'update']),
            permit(PermissionConstant::ROLE_DELETE, only: ['destroy']),
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

        $this->activityLogService->visited('roles list');

        return view('admin.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->activityLogService->visited('create role');

        $permissions = Permission::all(['id', 'name', 'label']);
        $roles = Role::all(['id', 'name']);

        return view('admin.roles.create', compact('permissions', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->service->create($request);

        $this->activityLogService->created('role', $role);

        $this->flashToast('success', 'Role Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.roles.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $this->activityLogService->visited('role detail', $role);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $this->activityLogService->visited('edit role', $role);

        $permissions = Permission::all(['id', 'name', 'label']);
        $roles = Role::all(['id', 'name']);
        $rolePermissions = $role->permissions()->get(['id'])->keyBy('id');

        return view('admin.roles.edit', compact('permissions', 'role', 'roles', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $update = $this->service->update($role, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: ['type' => 'warning', 'message' => 'No Changes Made!']);
        }

        $this->activityLogService->updated('role', $role);

        $this->flashToast('success', 'Role Updated!');

        return $this->responseService->json(success: true, redirectTo: route('admin.roles.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->service->delete($role);

        $this->activityLogService->deleted('role', $role);

        return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Role Deleted!']);
    }
}
