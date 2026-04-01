<?php

namespace App\Services;

use App\Http\Requests\Admin\RoleRequest;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class RoleService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = Role::query();

        $query->select(['id', 'name']);

        $query->withCount('permissions');

        $table = datatables()->eloquent($query);

        return $table;
    }

    public function create(RoleRequest $request): Role
    {
        return DB::transaction(function () use ($request) {
            $permissionNames = $request->validated('permissions', []);
            $role = Role::create($request->validated());
            $role->givePermissionTo($permissionNames);

            return $role;
        });
    }

    public function update(Role $role, RoleRequest $request): Role|bool
    {
        return DB::transaction(function () use ($role, $request) {

            $role->fill($request->validated());
            $role->user_creatable_roles = $request->validated('user_creatable_roles', []);
            $permissionNames = $request->validated('permissions', []);
            $rolePermissionNames = $role->permissions()->select(['name'])->pluck('name')->toArray();

            $hasChanges = false;

            if ($role->isDirty('name', 'is_admin', 'user_creatable_roles')) {
                $role->save();
                $hasChanges = true;
            }

            if (! array_equals($permissionNames, $rolePermissionNames)) {
                $role->syncPermissions($permissionNames);
                $hasChanges = true;
            }

            return $hasChanges ? $role : false;
        });
    }

    public function delete(Role $role): ?bool
    {
        return $role->delete();
    }
}
