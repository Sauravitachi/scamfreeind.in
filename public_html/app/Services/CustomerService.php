<?php

namespace App\Services;

use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;

class CustomerService extends Service
{
    public function dataTable(): EloquentDataTable
    {
        $query = Customer::query();

        $table = datatables()->eloquent($query);

        $table->addColumn('full_name', fn (Customer $customer): ?string => $customer->fullName);
        $table->addColumn('country_name', function (Customer $customer): ?string {
            if ($customer->country_code && ($country = country($customer->country_code))) {
                return $country->getEmoji().' '.$country->getName();
            }

            return null;
        });

        $table->editColumn('phone_number', fn (Customer $customer): string => $customer->fullPhoneNumber);
        $table->editColumn('created_at', fn (Customer $user): string => format_date($user->created_at));

        $table->orderColumn('full_name', function (Builder $query, string $order): void {
            $query->orderBy('first_name', $order)->orderBy('last_name', $order);
        });

        $table->filterColumn('full_name', function (Builder $query, string $keyword): void {
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"]);
            });
        });

        $table->orderColumn('created_at', function (Builder $query) {
            $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC');
        });

        return $table;
    }

    public function selectSearch(Request $request): Collection
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Customer::query()->whereSearch($search);

        $customers = $query->paginate($perPage, ['id', 'first_name', 'last_name', 'dial_code', 'phone_number'], 'page', $page);

        return collect([
            'records' => $customers->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'text' => $customer->fullNameWithFullPhoneNumber,
            ]),
            'has_more_pages' => $customers->hasMorePages(),
        ]);
    }

    public function create(CustomerRequest $request): Customer
    {
        $customer = Customer::create($request->validated());

        return $customer;
    }

    public function update(Customer $customer, CustomerRequest $request): bool
    {
        $customer->fill($request->validated());
        if ($customer->isDirty()) {
            return $customer->save();
        }

        return false;
    }

    public function isDeletable(Customer $customer): bool
    {
        return true;
    }

    public function delete(Customer $customer): ?bool
    {
        return DB::transaction(function () use ($customer): bool|null {
            $customer->load(['scams:id,customer_id']);
            foreach ($customer->scams as $scam) {
                $scam->scamFiles()->delete();
            }
            $customer->enquiries()->delete();
            $customer->scams()->delete();

            return $customer->delete();
        });
    }
}
