<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ActivityEvent;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\BulkDeleteScamLeadRequest;
use App\Http\Requests\Admin\BulkTransferScamLeadRequest;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\LawyerLead;
use App\Models\ScamSource;
use App\Models\ProblemType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LawyerController extends \App\Foundation\Controller
{
    /**
     * Constructor for LawyerController
     */
    public function __construct(
        protected ScamLeadService $service,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_LEAD_LIST, only: ['index', 'show']),
            permit(Permission::SCAM_LEAD_CREATE, only: ['create', 'store']),
            permit(Permission::SCAM_LEAD_UPDATE, only: ['edit', 'update']),
            permit(Permission::SCAM_LEAD_DELETE, only: ['destroy']),
            permit(Permission::SCAM_LEAD_TRANSFER, only: ['transfer']),
            permit(Permission::SCAM_LEAD_BULK_DELETE, only: ['bulkDelete']),
            permit(Permission::SCAM_LEAD_BULK_TRANSFER, only: ['bulkTransfer']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            $query = LawyerLead::query();
            return $this->service->dataTable($request, $query)->toJson();
        }

        $this->activityLogService->visited('lawyer lead list');

        $scamTypes = ProblemType::orderBy('title')->get(['id', 'title']);
        $scamSources = ScamSource::orderBy('title')->get(['id', 'title']);

        return view('admin.lawyer.index', compact('scamTypes', 'scamSources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create lawyer lead');

        $scamTypes = ProblemType::all();

        return view('admin.lawyer.create', compact('scamTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamLeadRequest $request): JsonResponse
    {
        $lawyer = $this->service->create($request, LawyerLead::class);

        $this->activityLogService->created('lawyer lead', $lawyer);

        $this->flashToast(new Toast('success', 'Lawyer Lead Created!'));

        return $this->responseService->json(success: true, redirectTo: route('admin.lawyer.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LawyerLead $lawyer): View
    {
        $this->activityLogService->visited('edit lawyer lead', $lawyer);

        $lawyer->load('scamSource');

        $scamTypes = ProblemType::all();

        return view('admin.lawyer.edit', compact('lawyer', 'scamTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamLeadRequest $request, LawyerLead $lawyer): JsonResponse
    {
        $update = $this->service->update($lawyer, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: new Toast('warning', 'No Changes Made!'));
        }

        $this->activityLogService->updated('lawyer-lead', $lawyer);

        $toast = new Toast('success', 'Lawyer Lead Updated!');

        if ($request->has('toast')) {
            return $this->responseService->json(success: true, toast: $toast);
        }

        $this->flashToast($toast);

        return $this->responseService->json(success: true, redirectTo: route('admin.lawyer.index'));
    }

    /**
     * show the specified resource
     */
    public function show(LawyerLead $lawyer): JsonResponse
    {
        return $this->responseService->json(success: true, data: $lawyer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LawyerLead $lawyer): JsonResponse
    {
        $this->service->delete($lawyer);
        $this->activityLogService->deleted('lawyer lead', $lawyer);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Lawyer Lead deleted!'));
    }

    /**
     * Transfer lawyer lead to main client list
     */
    public function transfer(LawyerLead $lawyer): JsonResponse
    {
        if (! $this->service->validateTransfer($lawyer)) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Lead with errors cannot be transferred!'));
        }

        if (! $this->service->transfer($lawyer)) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Duplicate lead transfer is not allowed. Create it manually.'));
        }

        $this->activityLogService->log(description: 'lawyer lead', event: ActivityEvent::TRANSFERRED, entity: $lawyer);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Lead Transferred!'));
    }

    /**
     * Bulk delete lawyer leads
     */
    public function bulkDelete(BulkDeleteScamLeadRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request, LawyerLead::class);
        $this->activityLogService->log(description: 'lawyer lead', event: ActivityEvent::BULK_DELETED, properties: $request->validated('ids'));

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads deleted!'));
    }

    /**
     * Bulk transfer lawyer leads
     */
    public function bulkTransfer(BulkTransferScamLeadRequest $request): JsonResponse
    {
        try {

            $this->service->bulkTransfer($request, LawyerLead::class);
            $this->activityLogService->log(description: 'lawyer lead', event: ActivityEvent::BULK_TRANSFER, properties: $request->validated('ids'));

        } catch (InvalidRequestException $e) {

            return $this->responseService->json(success: true, toast: new Toast('warning', 'None of the selected leads were valid!'));

        }

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads transferred!'));
    }

    public function similarLeads(LawyerLead $lawyer): JsonResponse
    {
        $leads = LawyerLead::query()
            ->with([
                'scamSource:id,title',
                'scamType:id,title',
                'existingCustomer:id',
            ])
            ->whereNot('id', $lawyer->id)
            ->where('phone_number', $lawyer->phone_number)
            ->where('country_code', $lawyer->country_code)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->get();

        $html = view('admin.lawyer.ssr.duplicate_leads_table', ['leads' => $leads]);

        return $this->responseService->json(success: true, html: $html, data: [
            'count' => $leads->count(),
        ]);
    }
}
