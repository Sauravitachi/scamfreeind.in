<?php

namespace App\Services;

use App\Http\Requests\Admin\ScamStatusRequest;
use App\Models\ScamStatus;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class ScamStatusService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = ScamStatus::query();
        $table = datatables()->eloquent($query);
        $table->editColumn('type', fn (ScamStatus $scamStatus) => ucfirst($scamStatus->type->value));

        return $table;
    }

    public function create(ScamStatusRequest $request): ScamStatus
    {
        return DB::transaction(function () use ($request): ScamStatus {
            $status = ScamStatus::create($request->safe()->except('updatable_fields'));
            $updatableFields = $request->validated('updatable_fields', []);
            $status->statusUpdateFields()->delete();
            $status->statusUpdateFields()->createMany($updatableFields);

            return $status;
        });
    }

    public function delete(ScamStatus $scamStatus): ?bool
    {
        return $scamStatus->delete();
    }

    public function update(ScamStatus $scamStatus, ScamStatusRequest $request): bool
    {
        return DB::transaction(function () use ($scamStatus, $request) {
            $scamStatus->update($request->safe()->except('updatable_fields'));
            $updatableFields = $request->validated('updatable_fields', []);

            $scamStatus->statusUpdateFields()->delete();
            $scamStatus->statusUpdateFields()->createMany($updatableFields);

            return true;
        });
    }
}
