<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\DTO\Toast;
use App\Http\Requests\Admin\CustomerEnquiryStatusRequest;
use App\Models\CustomerEnquiryStatus;
use App\Services\ActivityLogService;
use App\Services\CustomerEnquiryStatusService;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerEnquiryStatusController extends \App\Foundation\Controller
{
    /**
     * Constructor for CustomerEnquiryStatusController
     */
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected CustomerEnquiryStatusService $service,
        protected ResponseService $responseService,
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::CUSTOMER_ENQUIRY_STATUS_LIST, only: ['index', 'show']),
            permit(Permission::CUSTOMER_ENQUIRY_STATUS_CREATE, only: ['create', 'store']),
            permit(Permission::CUSTOMER_ENQUIRY_STATUS_UPDATE, only: ['edit', 'update']),
            permit(Permission::CUSTOMER_ENQUIRY_STATUS_DELETE, only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable()->toJson();
        }

        $this->activityLogService->visited('customer enquiry status list');

        return view('admin.customer-enquiry-statuses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create customer enquiry status');

        return view('admin.customer-enquiry-statuses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerEnquiryStatusRequest $request): JsonResponse
    {
        $customerEnquiryStatus = $this->service->create($request);

        $this->activityLogService->created('customer enquiry status', $customerEnquiryStatus);

        $this->flashToast('success', 'customer enquiry Status Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.customer-enquiry-statuses.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerEnquiryStatus $customerEnquiryStatus): View
    {
        $this->activityLogService->visited('customer enquiry status detail', $customerEnquiryStatus);

        return view('admin.customer-enquiry-statuses.show', compact('customerEnquiryStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerEnquiryStatus $customerEnquiryStatus): View
    {
        $this->activityLogService->visited('edit customer enquiry status', $customerEnquiryStatus);

        return view('admin.customer-enquiry-statuses.edit', compact('customerEnquiryStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerEnquiryStatusRequest $request, CustomerEnquiryStatus $customerEnquiryStatus): JsonResponse
    {
        $updated = $this->service->update($customerEnquiryStatus, $request);
        if ($updated) {
            $toast = new Toast(type: 'success', message: 'customer enquiry Status Updated!');
            $this->activityLogService->updated('customer enquiry status', $customerEnquiryStatus);
        } else {
            $toast = new Toast(type: 'warning', message: 'No Changes Made!');
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerEnquiryStatus $customerEnquiryStatus): JsonResponse
    {
        $this->service->delete($customerEnquiryStatus);

        $this->activityLogService->deleted('customer enquiry status', $customerEnquiryStatus);

        return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'customer enquiry Status Deleted!']);
    }
}
