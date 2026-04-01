<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ActivityEvent;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\BulkDeleteScamLeadRequest;
use App\Http\Requests\Admin\BulkTransferScamLeadRequest;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\ScamLead;
use App\Models\ScamSource;
use App\Models\ScamType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScamLeadController extends \App\Foundation\Controller
{
    /**
     * Constructor for ScamLeadController
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
            return $this->service->dataTable($request)->toJson();
        }

        $this->activityLogService->visited('scam lead list');

        $scamTypes = ScamType::orderBy('title')->get(['id', 'title']);
        $scamSources = ScamSource::orderBy('title')->get(['id', 'title']);

        return view('admin.scam-leads.index', compact('scamTypes', 'scamSources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create scam lead');

        $scamTypes = ScamType::all();

        return view('admin.scam-leads.create', compact('scamTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamLeadRequest $request): JsonResponse
    {
        $scamLead = $this->service->create($request);

        $this->activityLogService->created('scam lead', $scamLead);

        $this->flashToast(new Toast('success', 'Scam Lead Created!'));

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-leads.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ScamLead $scamLead): View
    {
        $this->activityLogService->visited('edit scam lead', $scamLead);

        $scamLead->load('scamSource');

        $scamTypes = ScamType::all();

        return view('admin.scam-leads.edit', compact('scamLead', 'scamTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamLeadRequest $request, ScamLead $scamLead): JsonResponse
    {
        $update = $this->service->update($scamLead, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: new Toast('warning', 'No Changes Made!'));
        }

        $this->activityLogService->updated('scam-lead', $scamLead);

        $toast = new Toast('success', 'Scam Lead Updated!');

        if ($request->has('toast')) {
            return $this->responseService->json(success: true, toast: $toast);
        }

        $this->flashToast($toast);

        return $this->responseService->json(success: true, redirectTo: route('admin.scam-leads.index'));
    }

    /**
     * show the specified resource
     */
    public function show(ScamLead $scamLead): JsonResponse
    {
        return $this->responseService->json(success: true, data: $scamLead);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ScamLead $scamLead): JsonResponse
    {
        $this->service->delete($scamLead);
        $this->activityLogService->deleted('scam lead', $scamLead);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Scam Lead deleted!'));
    }

    /**
     * Transfer scam lead to main scam list
     */
    public function transfer(ScamLead $scamLead): JsonResponse
    {
        if (! $this->service->validateTransfer($scamLead)) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Lead with errors cannot be transferred!'));
        }

        if (! $this->service->transfer($scamLead)) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Duplicate lead transfer is not allowed. Craete it manually.'));
        }

        $this->activityLogService->log(description: 'scam lead', event: ActivityEvent::TRANSFERRED, entity: $scamLead);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Lead Transferred!'));
    }

    /**
     * Bulk delete scam leads
     */
    public function bulkDelete(BulkDeleteScamLeadRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request);
        $this->activityLogService->log(description: 'scam lead', event: ActivityEvent::BULK_DELETED, properties: $request->validated('ids'));

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads deleted!'));
    }

    /**
     * Bulk transfer scam leads
     */
    public function bulkTransfer(BulkTransferScamLeadRequest $request): JsonResponse
    {
        try {

            $this->service->bulkTransfer($request);
            $this->activityLogService->log(description: 'scam lead', event: ActivityEvent::BULK_TRANSFER, properties: $request->validated('ids'));

        } catch (InvalidRequestException $e) {

            return $this->responseService->json(success: true, toast: new Toast('warning', 'None of the selected leads were valid!'));

        }

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads transferred!'));
    }

    public function similarLeads(ScamLead $scamLead): JsonResponse
    {
        $leads = ScamLead::query()
            ->with([
                'scamSource:id,title',
                'scamType:id,title',
                'existingCustomer:id',
            ])
            ->whereNot('id', $scamLead->id)
            ->where('phone_number', $scamLead->phone_number)
            ->where('country_code', $scamLead->country_code)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->get();

        $html = view('admin.scam-leads.ssr.duplicate_leads_table', ['leads' => $leads]);

        return $this->responseService->json(success: true, html: $html, data: [
            'count' => $leads->count(),
        ]);
    }
}
