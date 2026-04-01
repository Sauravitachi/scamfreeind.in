<?php

namespace App\Services;

use App\Http\Requests\Admin\PermissionRequest;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;

class PermissionService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = Permission::query();
        $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');

        $table = datatables()->eloquent($query);

        $table->orderColumn('created_at', function (Builder $query) {
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        });

        return $table;
    }

    public function update(Permission $permission, PermissionRequest $request): Permission|bool
    {
        $permission->fill($request->validated());

        if ($permission->isDirty()) {
            $permission->save();

            return $permission;
        }

        return false;
    }
}
