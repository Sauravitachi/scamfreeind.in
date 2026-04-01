<?php

namespace App\Services;

use App\Http\Requests\Admin\CustomerEnquiryStatusRequest;
use App\Models\CustomerEnquiryStatus;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class CustomerEnquiryStatusService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = CustomerEnquiryStatus::query();

        $table = datatables()->eloquent($query);

        $table->editColumn('type', fn (CustomerEnquiryStatus $status) => $status->type->label());

        return $table;
    }

    public function create(CustomerEnquiryStatusRequest $request): CustomerEnquiryStatus
    {
        return DB::transaction(fn (): CustomerEnquiryStatus => CustomerEnquiryStatus::create($request->validated()));
    }

    public function update(CustomerEnquiryStatus $status, CustomerEnquiryStatusRequest $request): CustomerEnquiryStatus|bool
    {
        $status->fill($request->validated());

        return DB::transaction(function () use ($status): bool|CustomerEnquiryStatus {
            if ($status->isDirty()) {
                $status->save();

                return $status;
            }

            return false;
        });
    }

    public function delete(CustomerEnquiryStatus $status): ?bool
    {
        return $status->delete();
    }
}
