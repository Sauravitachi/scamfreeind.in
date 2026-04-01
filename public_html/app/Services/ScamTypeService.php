<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\ScamTypeRequest;
use App\Models\ScamType;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;

class ScamTypeService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = ScamType::query();

        $table = datatables()->eloquent($query);

        $table->orderColumn('created_at', function (Builder $query) {
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        });

        return $table;
    }

    public function create(ScamTypeRequest $request): ScamType
    {
        return ScamType::create($request->validated());
    }

    public function delete(ScamType $scamType): ?bool
    {
        if ($scamType->is_default) {
            throw new InvalidRequestException('Cannot delete type with default scam type.');
        }

        return $scamType->delete();
    }

    public function update(ScamType $scamType, ScamTypeRequest $request): ScamType|bool
    {
        $scamType->fill($request->validated());
        if ($scamType->isDirty()) {
            if ($scamType->isDirty('is_default') && ! $scamType->is_default) {
                throw new InvalidRequestException('Is Default must be enabled for 1 type!');
            }
            $scamType->save();

            return $scamType;
        }

        return false;
    }
}
