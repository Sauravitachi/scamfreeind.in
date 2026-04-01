<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use App\Services\ActivityLogService;
use App\Services\CustomerService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CustomerController extends \App\Foundation\Controller
{
    /**
     * Constructor for CustomerController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected ResponseService $responseService,
        protected CustomerService $service,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::CUSTOMER_LIST, only: ['index', 'show']),
            permit(Permission::CUSTOMER_CREATE, only: ['create', 'store']),
            permit(Permission::CUSTOMER_UPDATE, only: ['edit', 'update']),
            permit(Permission::CUSTOMER_DELETE, only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('customers list');

        return view('admin.customers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->activityLogService->visited('create customer');

        return view('admin.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $customer = $this->service->create($request);

        $this->activityLogService->created('customer', $customer);

        $this->flashToast('success', 'Customer Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.customers.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $this->activityLogService->visited('customer detail', $customer);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $this->activityLogService->visited('edit customer', $customer);

        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        $update = $this->service->update($customer, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: ['type' => 'warning', 'message' => 'No Changes Made!']);
        }

        $this->activityLogService->updated('customer', $customer);

        $this->flashToast('success', 'Customer Updated!');

        return $this->responseService->json(success: true, redirectTo: route('admin.customers.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if ($this->service->isDeletable($customer) && $this->service->delete($customer)) {
            $toast = ['type' => 'success', 'message' => 'Customer deleted!'];
            $this->activityLogService->deleted('customer', $customer);
        } else {
            $toast = ['type' => 'warning', 'message' => 'Customer can\'t be removed.'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Search for the customer
     */
    public function selectSearch(Request $request)
    {
        $results = $this->service->selectSearch($request);

        return $this->responseService->json(success: true, data: $results);
    }
}
