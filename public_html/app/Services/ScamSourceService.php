<?php

namespace App\Services;

use App\Http\Requests\Admin\ScamSourceRequest;
use App\Models\CustomerEnquiry;
use App\Models\Scam;
use App\Models\ScamLead;
use App\Models\ScamSource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\EloquentDataTable;

class ScamSourceService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = ScamSource::query();

        $table = datatables()->eloquent($query);

        return $table;
    }

    public function create(ScamSourceRequest $request): ScamSource
    {
        return ScamSource::create($request->validated());
    }

    public function isDeletable(ScamSource $scamSource): bool
    {
        $models = [Scam::class, ScamLead::class, CustomerEnquiry::class];
        foreach ($models as $model) {
            if ($model::where('scam_source_id', $scamSource->id)->exists()) {
                return false;
            }
        }

        return true;
    }

    public function delete(ScamSource $scamSource): ?bool
    {
        return $scamSource->delete();
    }

    public function update(ScamSource $scamSource, ScamSourceRequest $request): ScamSource|bool
    {
        $scamSource->fill($request->validated());
        if ($scamSource->isDirty()) {
            $scamSource->save();

            return $scamSource;
        }

        return false;
    }

    public function selectSearch(Request $request): Collection
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = ScamSource::query()
            ->where('slug', 'LIKE', "%$search%")
            ->orWhere('title', 'LIKE', "%$search%");

        $sources = $query->paginate($perPage, ['id', 'title'], 'page', $page);

        return collect([
            'records' => $sources->map(function (ScamSource $source) {

                $text = $source->title;

                return [
                    'id' => $source->id,
                    'text' => $text,
                ];

            }),
            'has_more_pages' => $sources->hasMorePages(),
        ]);
    }
}
