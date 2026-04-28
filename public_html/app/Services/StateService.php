<?php

namespace App\Services;

use App\Http\Requests\Admin\StateRequest;
use App\Models\Customer;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\EloquentDataTable;

class StateService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = State::query();

        $table = datatables()->eloquent($query);

        return $table;
    }

    public function create(StateRequest $request): State
    {
        return State::create($request->validated());
    }

    public function isDeletable(State $state): bool
    {
        // Check if state is used in customers
        if (Customer::where('state', $state->id)->exists()) {
            return false;
        }

        return true;
    }

    public function delete(State $state): ?bool
    {
        return $state->delete();
    }

    public function update(State $state, StateRequest $request): State|bool
    {
        $state->fill($request->validated());
        if ($state->isDirty()) {
            $state->save();

            return $state;
        }

        return false;
    }

    public function selectSearch(Request $request): Collection
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = State::query()
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('code', 'LIKE', "%$search%");
            });

        $states = $query->paginate($perPage, ['id', 'name'], 'page', $page);

        return collect([
            'records' => $states->map(function (State $state) {
                return [
                    'id' => $state->id,
                    'text' => $state->name,
                ];
            }),
            'has_more_pages' => $states->hasMorePages(),
        ]);
    }
}
