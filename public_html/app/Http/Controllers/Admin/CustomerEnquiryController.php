<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permission;
use App\Enums\ScamAssigneeType;
use App\Enums\ScamStatusType;
use App\Http\Requests\Admin\ChangeCustomerEnquiryStatusRequest;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryStatus;
use App\Models\ScamStatus;
use App\Models\ScamType;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\CustomerEnquiryService;
use App\Services\HelperService;
use App\Services\ResponseService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerEnquiryController extends \App\Foundation\Controller
{
    public function __construct(
        protected CustomerEnquiryService $service,
        protected HelperService $helperService,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
        protected UserService $userService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::CUSTOMER_ENQUIRY_LIST, only: ['index', 'show']),
            permit(Permission::CUSTOMER_ENQUIRY_UPDATE_STATUS, only: ['changeStatus']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        $assigneeType = $request->input('type', ScamAssigneeType::SALES->value) === ScamAssigneeType::SALES->value
            ? ScamAssigneeType::SALES->value
            : ScamAssigneeType::DRAFTING->value;

        $request->merge(['assigneeType' => $assigneeType]);

        if ($request->ajax()) {
            return $this->service->dataTable($request)->toJson();
        }

        $user = $request->user();
        $userRole = $user->getRoleString();

        $this->activityLogService->visited('customer enquiry list');

        $customerEnquiryStatuses = CustomerEnquiryStatus::orderBy('title')->get(['id', 'title', 'type', 'is_remark_required', 'consider_resolved']);
        $salesUsers = User::whereSales()->orderBy('name')->get(['id', 'name', 'status']);
        $draftingUsers = User::whereDrafting()->orderBy('name')->get(['id', 'name', 'status']);
        $scamStatuses = ScamStatus::withExists('statusUpdateFields')
            ->with(['previousStatuses', 'nextStatuses'])->orderBy('title')->get();
        $firstDraftingStatus = $scamStatuses->where('type', ScamStatusType::DRAFTING)->sortBy('index')->first();
        $scamTypes = ScamType::orderBy('title')->get(['id', 'title']);

        $hasFrozenEnquiries = $this->service->hasFrozenEnquiries($user, 'drafting');

        return view('admin.customer-enquiries.index', compact(
            'assigneeType',
            'customerEnquiryStatuses',
            'salesUsers',
            'draftingUsers',
            'hasFrozenEnquiries',
            'scamStatuses',
            'firstDraftingStatus',
            'scamTypes'
        ));
    }

    public function show(Request $request, CustomerEnquiry $customerEnquiry): View
    {
        $user = $request->user();
        $userType = $user->userType();

        $customerEnquiry->load([
            'customer:id,first_name,last_name,dial_code,country_code,phone_number,email,created_at',
            'salesStatus:id,title',
            'draftingStatus:id,title',
            'customer.scams' => function (HasMany $q) use ($user, $userType): HasMany {

                $q->select(['customer_id', 'scam_type_id', 'scam_amount', 'sales_assignee_id', 'drafting_assignee_id', 'sales_assigned_at', 'drafting_assigned_at', 'created_at'])
                    ->with([
                        'scamType',
                        'salesAssignee',
                        'draftingAssignee',
                    ]);

                if ($userType === 'sales') {
                    $q->whereNotNull('sales_assignee_id')->where('sales_assignee_id', $user->id);
                } elseif ($userType === 'drafting') {
                    $q->whereNotNull('drafting_assignee_id')->where('drafting_assignee_id', $user->id);
                }

                $q->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');

                return $q;
            },
            'source:id,title',
        ]);

        $allCustomerEnquiries = $customerEnquiry->customer->enquiries()
            ->with([
                'records' => fn (HasMany $q) => $q->latest(),
                'records.status:id,title',
                'records.causer:id,name,username',
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        abort_if(boolean: $userType !== 'admin' && $customerEnquiry->customer->scams->isEmpty(), code: 404);

        $this->helperService->requestNotificationMarkAsRead($request);

        return view('admin.customer-enquiries.show', compact(
            'customerEnquiry',
            'allCustomerEnquiries'
        ));
    }

    /**
     * Change status of the enquiry
     */
    public function changeStatus(ChangeCustomerEnquiryStatusRequest $request, int $enquiryId): JsonResponse
    {
        $enquiry = CustomerEnquiry::findOrFail($enquiryId);

        $success = $this->service->changeStatus($request, $enquiry);

        $this->activityLogService->updated('customer enquiry status', $enquiry, [
            'customer_enquiry_id' => $enquiry->id,
            'status_id' => $request->status_id,
        ]);

        return $this->responseService->json(success: $success);
    }
}
