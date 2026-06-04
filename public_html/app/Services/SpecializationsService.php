<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\SpecializationRequest;
use App\Models\ProblemType;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\EloquentDataTable;

class SpecializationsService extends Service
{
    /**
     * Build DataTable query for Specializations (ProblemType).
     */
    public function dataTable(): EloquentDataTable
    {
        $query = ProblemType::query();

        $table = datatables()->eloquent($query);

        $table->orderColumn('created_at', function (Builder $query) {
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        });

        return $table;
    }

    /**
     * Create a Specialization.
     */
    public function create(SpecializationRequest $request): ProblemType
    {
        return ProblemType::create([
            'slug' => $request->validated('slug'),
            'title' => $request->validated('title'),
            'is_default' => $request->has('is_default') ? true : false,
        ]);
    }

    /**
     * Delete a Specialization.
     */
    public function delete(ProblemType $specialization): ?bool
    {
        if ($specialization->is_default) {
            throw new InvalidRequestException('Cannot delete default specialization.');
        }

        $specialization->lawyers()->detach();
        return $specialization->delete();
    }

    /**
     * Update a Specialization.
     */
    public function update(ProblemType $specialization, SpecializationRequest $request): ProblemType|bool
    {
        $specialization->fill([
            'slug' => $request->validated('slug'),
            'title' => $request->validated('title'),
            'is_default' => $request->has('is_default') ? true : false,
        ]);

        if ($specialization->isDirty()) {
            if ($specialization->isDirty('is_default') && !$specialization->is_default) {
                throw new InvalidRequestException('Is Default must be enabled for at least 1 specialization!');
            }
            $specialization->save();

            return $specialization;
        }

        return false;
    }
}
