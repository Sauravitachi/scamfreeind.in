<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ActivityEvent;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\BulkDeleteScamLeadRequest;
use App\Http\Requests\Admin\BulkTransferScamLeadRequest;
use App\Http\Requests\Admin\ScamLeadRequest;
use App\Models\VltradingLead;
use App\Models\ScamSource;
use App\Models\ScamType;
use App\Services\ActivityLogService;
use App\Services\ResponseService;
use App\Services\ScamLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VltradingController extends \App\Foundation\Controller
{
    /**
     * Constructor for VltradingController
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
            $query = VltradingLead::query();
            return $this->service->dataTable($request, $query)->toJson();
        }

        $this->activityLogService->visited('vltrading lead list');

        $scamTypes = ScamType::orderBy('title')->get(['id', 'title']);
        $scamSources = ScamSource::orderBy('title')->get(['id', 'title']);

        return view('admin.vltrading.index', compact('scamTypes', 'scamSources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create vltrading lead');

        $scamTypes = ScamType::orderBy('title')->get();

        return view('admin.vltrading.create', compact('scamTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamLeadRequest $request): JsonResponse
    {
        $vltrading = $this->service->create($request, VltradingLead::class);

        $this->activityLogService->created('vltrading lead', $vltrading);

        $this->flashToast(new Toast('success', 'VLTrading Lead Created!'));

        return $this->responseService->json(success: true, redirectTo: route('admin.vltrading.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VltradingLead $vltrading): View
    {
        $this->activityLogService->visited('edit vltrading lead', $vltrading);

        $vltrading->load('scamSource');

        $scamTypes = ScamType::orderBy('title')->get();

        return view('admin.vltrading.edit', compact('vltrading', 'scamTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamLeadRequest $request, VltradingLead $vltrading): JsonResponse
    {
        $update = $this->service->update($vltrading, $request);
        if (! $update) {
            return $this->responseService->json(success: true, toast: new Toast('warning', 'No Changes Made!'));
        }

        $this->activityLogService->updated('vltrading-lead', $vltrading);

        $toast = new Toast('success', 'VLTrading Lead Updated!');

        if ($request->has('toast')) {
            return $this->responseService->json(success: true, toast: $toast);
        }

        $this->flashToast($toast);

        return $this->responseService->json(success: true, redirectTo: route('admin.vltrading.index'));
    }

    /**
     * show the specified resource
     */
    public function show(VltradingLead $vltrading): JsonResponse
    {
        return $this->responseService->json(success: true, data: $vltrading);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VltradingLead $vltrading): JsonResponse
    {
        $this->service->delete($vltrading);
        $this->activityLogService->deleted('vltrading lead', $vltrading);

        return $this->responseService->json(success: true, toast: new Toast('success', 'VLTrading Lead deleted!'));
    }

    /**
     * Transfer vltrading lead to main client list
     */
    public function transfer(VltradingLead $vltrading): JsonResponse
    {
        if (! $this->service->validateTransfer($vltrading)->isValid()) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Lead with errors cannot be transferred!'));
        }

        if (! $this->service->transfer($vltrading)) {
            return $this->responseService->json(success: false, toast: new Toast('error', 'Duplicate lead transfer is not allowed. Create it manually.'));
        }

        $this->activityLogService->log(description: 'vltrading lead', event: ActivityEvent::TRANSFERRED, entity: $vltrading);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Lead Transferred!'));
    }

    /**
     * Bulk delete vltrading leads
     */
    public function bulkDelete(BulkDeleteScamLeadRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request, VltradingLead::class);
        $this->activityLogService->log(description: 'vltrading lead', event: ActivityEvent::BULK_DELETED, properties: $request->validated('ids'));

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads deleted!'));
    }

    /**
     * Bulk transfer vltrading leads
     */
    public function bulkTransfer(BulkTransferScamLeadRequest $request): JsonResponse
    {
        try {
            $this->service->bulkTransfer($request, VltradingLead::class);
            $this->activityLogService->log(description: 'vltrading lead', event: ActivityEvent::BULK_TRANSFER, properties: $request->validated('ids'));
        } catch (InvalidRequestException $e) {
            return $this->responseService->json(success: true, toast: new Toast('warning', 'None of the selected leads were valid!'));
        }

        return $this->responseService->json(success: true, toast: new Toast('success', 'Leads transferred!'));
    }

    /**
     * Get similar leads
     */
    public function similarLeads(VltradingLead $vltrading): JsonResponse
    {
        $leads = VltradingLead::query()
            ->with([
                'scamSource:id,title',
                'scamType:id,title',
                'existingCustomer:id',
            ])
            ->whereNot('id', $vltrading->id)
            ->where('phone_number', $vltrading->phone_number)
            ->where('country_code', $vltrading->country_code)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->get();

        $html = view('admin.vltrading.ssr.duplicate_leads_table', ['leads' => $leads]);

        return $this->responseService->json(success: true, html: $html, data: [
            'count' => $leads->count(),
        ]);
    }
}
