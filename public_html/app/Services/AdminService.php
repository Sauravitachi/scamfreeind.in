<?php

namespace App\Services;

use App\Http\Requests\Admin\AdminRequest;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class AdminService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = Admin::with([
            'roles' => fn (MorphToMany $query) => $query->orderBy('priority', 'asc'),
        ])->withCount('roles');

        return datatables()->eloquent($query);
    }

    public function create(AdminRequest $request): Admin
    {
        $admin = Admin::create($request->validated());
        $admin->assignRole($request->validated('roles', []));

        return $admin;
    }

    public function update(Admin $admin, AdminRequest $request): Admin|bool
    {
        return DB::transaction(function () use ($admin, $request) {

            $admin->fill($request->validated());
            $roleNames = $request->validated('roles', []);
            $adminRoleNames = $admin->roles()->select(['name'])->pluck('name')->toArray();
            $hasChanges = false;

            if ($admin->isDirty()) {
                $admin->save();
                $hasChanges = true;
            }

            if (! array_equals($roleNames, $adminRoleNames)) {
                $admin->syncRoles($roleNames);
                $hasChanges = true;
            }

            return $hasChanges ? $admin : false;
        });
    }

    public function delete(Admin $admin): ?bool
    {
        return $admin->delete();
    }
}
