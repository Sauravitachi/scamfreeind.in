<?php

namespace App\Services;

use App\Http\Requests\Admin\LawyerRequest;
use App\Models\Lawyer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\EloquentDataTable;

class LawyersService extends Service
{
    /**
     * Build DataTable query for Lawyers.
     */
    public function dataTable(): EloquentDataTable
    {
        $query = Lawyer::query()->with('specializations');

        $table = datatables()->eloquent($query);

        $table->orderColumn('created_at', function (Builder $query) {
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        });

        return $table;
    }

    /**
     * Create a new Lawyer.
     */
    public function create(LawyerRequest $request): Lawyer
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('lawyers', 'public');
        }

        $lawyer = Lawyer::create($data);

        if ($request->has('specializations')) {
            $lawyer->specializations()->sync($request->validated('specializations'));
        }

        return $lawyer;
    }

    /**
     * Delete a Lawyer.
     */
    public function delete(Lawyer $lawyer): ?bool
    {
        $lawyer->specializations()->detach();
        if ($lawyer->image) {
            Storage::disk('public')->delete($lawyer->image);
        }
        return $lawyer->delete();
    }

    /**
     * Update a Lawyer.
     */
    public function update(Lawyer $lawyer, LawyerRequest $request): Lawyer|bool
    {
        $lawyer->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        if ($request->hasFile('image')) {
            if ($lawyer->image) {
                Storage::disk('public')->delete($lawyer->image);
            }
            $lawyer->image = $request->file('image')->store('lawyers', 'public');
        }

        $dirty = $lawyer->isDirty();
        $lawyer->save();

        if ($request->has('specializations')) {
            $synced = $lawyer->specializations()->sync($request->validated('specializations'));
            if (count($synced['attached']) > 0 || count($synced['detached']) > 0 || count($synced['updated']) > 0) {
                $dirty = true;
            }
        }

        return $dirty ? $lawyer : false;
    }
}
