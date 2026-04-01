<?php

namespace App\Services;

use App\Http\Requests\Admin\ScamRegistrationAmountRequest;
use App\Models\ScamRegistration;
use App\Models\ScamRegistrationAmount;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

class ScamRegistrationAmountService extends Service
{
    public function dataTable(Request $request): EloquentDataTable
    {
        $query = ScamRegistrationAmount::query();

        $table = datatables()->eloquent($query);

        $table->editColumn('amount', fn ($a) => format_amount($a->amount));

        return $table;
    }

    public function create(ScamRegistrationAmountRequest $request): ScamRegistrationAmount
    {
        return ScamRegistrationAmount::create($request->validated());
    }

    public function update(ScamRegistrationAmount $scamRegistrationAmount, ScamRegistrationAmountRequest $request): bool
    {
        $scamRegistrationAmount->fill($request->validated());

        if (! $scamRegistrationAmount->isDirty()) {
            return false;
        }

        $scamRegistrationAmount->save();

        return true;
    }

    public function isDeletable(ScamRegistrationAmount $scamRegistrationAmount): bool
    {
        if (ScamRegistration::where('scam_registration_amount_id', $scamRegistrationAmount->id)->exists()) {
            return false;
        }

        return true;
    }

    public function delete(ScamRegistrationAmount $scamRegistrationAmount): bool
    {
        return $scamRegistrationAmount->delete();
    }
}
