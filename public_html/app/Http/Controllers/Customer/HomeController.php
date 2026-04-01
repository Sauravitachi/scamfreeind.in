<?php

namespace App\Http\Controllers\Customer;

use App\DTO\Toast;
use App\Enums\ScamStatusType;
use App\Http\Requests\Customer\RaiseEnquiryRequest;
use App\Models\Scam;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends \App\Foundation\Controller
{
    public function __construct(
        protected ResponseService $responseService
    ) {}

    public function index(Request $request): View
    {
        $customer = \App\Models\Customer::find(session('customer_id'));
        $scams = $customer ? $customer->mainRegisteredScams()
            ->with([
                'scamType',
                'draftingAssignee:id,name,dial_code,phone_number',
                'draftingStatus:id,title',
                'statusRecords' => function (HasMany $q) {
                    $q->select(['id', 'scam_id', 'status_id', 'status_type'])
                        ->where('status_type', ScamStatusType::DRAFTING)
                        ->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                },
                'statusRecords.status:id,title',
                'scamStatusFiles.file',
            ])
            ->get() : collect();

        return view('customer.home.index', compact('scams'));
    }

    public function raiseEnquiry(RaiseEnquiryRequest $request, ScamLeadService $scamLeadService): JsonResponse
    {
        $scam = Scam::find($request->validated('scam_id'), ['id', 'customer_id']);
        $customer = $scam ? \App\Models\Customer::find($scam->customer_id) : null;

        if ($customer) {
            $scamLeadService->createCustomerEnquiry($customer, 'User Panel', $request->string('query'));
        }

        return $this->responseService->json(success: true, toast: new Toast('success', 'Enquiry has been received!'));
    }
}
