<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Scams\BulkAssignUsers;
use App\Actions\Scams\BulkRecycleScam;
use App\Actions\Scams\ChangeScamStatusReview;
use App\Actions\Scams\ChangeStatus;
use App\Actions\Scams\ImportScamRecords;
use App\Actions\Scams\RandomScamAssign;
use App\Actions\Scams\UpdateStatusWithData;
use App\Constants\ActivityEvent;
use App\Constants\FileDirectory;
use App\Constants\Permission;
use App\DTO\Toast;
use App\Enums\ScamStatusType;
use App\Exceptions\ExcelFileValidationException;
use App\Filters\ScamFilter;
use App\Http\Requests\Admin\AssignUserToScamRequest;
use App\Http\Requests\Admin\BulkAssignUserToScamRequest;
use App\Http\Requests\Admin\BulkRecycleScamRequest;
use App\Http\Requests\Admin\BulkUpdateScamRequest;
use App\Http\Requests\Admin\ChangeScamStatusRequest;
use App\Http\Requests\Admin\ChangeScamStatusReviewRequest;
use App\Http\Requests\Admin\RandomScamAssignRequest;
use App\Http\Requests\Admin\ScamFileImportRequest;
use App\Http\Requests\Admin\ScamImportFileScanRequest;
use App\Http\Requests\Admin\ScamRequest;
use App\Http\Requests\Admin\UpdateStatusDataRequest;
use App\Http\Requests\Admin\UploadScamFilesRequest;
use App\Models\Escalation;
use App\Models\Scam;
use App\Models\ScamFile;
use App\Models\ScamSource;
use App\Models\ScamStatus;
use App\Models\ScamStatusFile;
use App\Models\ScamType;
use App\Models\State;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\CustomerEnquiryService;
use App\Services\ResponseService;
use App\Services\ScamService;
use App\Services\UploadedFileService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScamExport;
use Illuminate\Support\Facades\Log;

class ScamController extends \App\Foundation\Controller
{
    /**
     * Constructor for CustomerController
     *
     * @param  \App\Services\ScamService  $servic
     */
    public function __construct(
        protected ScamService $service,
        protected UserService $userService,
        protected ResponseService $responseService,
        protected ActivityLogService $activityLogService,
        protected UploadedFileService $uploadedFileService
    ) {}

    /**
     * Permission middleware for resource controller methods
     */
    public static function middleware(): array
    {
        return [
            permit(Permission::SCAM_LIST, only: ['index']),
            permit([Permission::SCAM_LIST, Permission::SUB_ADMIN_MANAGEMENT], only: ['show']),
            permit(Permission::SCAM_CREATE, only: ['create', 'store']),
            permit(Permission::SCAM_UPDATE, only: ['edit', 'update']),
            permit(Permission::SCAM_DELETE, only: ['destroy']),
            // Assignment used by sales/drafting/service managers and (now) sub‑admin managers
            permit([
                Permission::SALES_MANAGEMENT,
                Permission::DRAFTING_MANAGEMENT,
                Permission::SERVICE_MANAGEMENT,
                Permission::SUB_ADMIN_MANAGEMENT,          // added so sub‑admins can hit the endpoint as well
            ], only: ['assignUser', 'bulkAssignUsers']),
            permit([Permission::SALES_MANAGEMENT, Permission::SALES_MANAGEMENT_SELF, Permission::DRAFTING_MANAGEMENT, Permission::DRAFTING_MANAGEMENT_SELF], only: ['changeStatus']),
            permit([Permission::SCAM_SALES_STATUS_REVIEW_UPDATE, Permission::SCAM_DRAFTING_STATUS_REVIEW_UPDATE], only: ['changeScamStatusReview']),
            permit(Permission::SCAM_LIST, only: ['changeState']),
            permit([Permission::SCAM_LIST, Permission::ESCALATION_LIST, Permission::ESCALATION_LIST_SELF], only: ['selectSearch', 'allScamEscalations']),
            permit(Permission::DELETE_SCAM_STATUS_FILE, only: ['deleteStatusFile', 'deleteScamFile']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CustomerEnquiryService $customerEnquiryService): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable($request)->toJson();
        }

        $user = $request->user();
        $userRole = $user->getRoleString();

        $hasFrozenEnquiries = $userRole && $customerEnquiryService->hasFrozenEnquiries($user, $userRole);

        if ($hasFrozenEnquiries) {
            return view('admin.scams.access-block');
        }

        $salesUsers = User::query()
            ->with('lastActivity', function ($q) {
                $q->select('id', 'activity_log.causer_id', 'activity_log.causer_type', 'created_at');
            })->whereSales()->orderBy('name')->get(['id', 'name', 'status'])->append('has_today_activity');

        $draftingUsers = User::whereDrafting()->orderBy('name')->get(['id', 'name', 'status']);
        $serviceUsers = User::whereService()->orderBy('name')->get(['id', 'name', 'status']);
        $scamStatuses = ScamStatus::withExists('statusUpdateFields')
            ->with(['previousStatuses', 'nextStatuses'])->orderBy('title')->get();
        $scamTypes = ScamType::orderBy('title')->get(['id', 'title']);
        $states = State::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $firstDraftingStatus = $scamStatuses->where('type', ScamStatusType::DRAFTING)->sortBy('index')->first();
        $scamSources = ScamSource::all(['id', 'title']);

        $hasFrozenStatus = $userRole &&
            (
                ScamStatus::where('is_freezable', true)->exists() ||
                setting("freeze_{$userRole}_null_threshold", null) !== null
            ) &&
            $this->service->hasFrozenStatus($user, $userRole);

        $this->activityLogService->visited('scams list');

        ScamFilter::setData();

        $reminderScams = $this->service->statusReminderScams(Auth::user());

        $subAdminUsers = User::role('Sub Admin')->orderBy('name')->get(['id', 'name', 'status']);

        return view('admin.scams.index', compact(
            'salesUsers',
            'scamStatuses',
            'draftingUsers',
            'serviceUsers',
            'firstDraftingStatus',
            'scamTypes',
            'scamSources',
            'hasFrozenStatus',
            'reminderScams',
            'subAdminUsers',
            'states'
        ));
    }

    public function subAdmin(Request $request, CustomerEnquiryService $customerEnquiryService): JsonResponse|View
    {
        if ($request->ajax()) {
            return $this->service->dataTable($request)->toJson();
        }

        $user = $request->user();
        $userRole = $user->getRoleString();

        $hasFrozenEnquiries = $userRole && $customerEnquiryService->hasFrozenEnquiries($user, $userRole);

        if ($hasFrozenEnquiries) {
            return view('admin.scams.access-block');
        }

        $salesUsersQuery = User::query()
            ->with('lastActivity', function ($q) {
                $q->select('id', 'activity_log.causer_id', 'activity_log.causer_type', 'created_at');
            })->whereSales()->orderBy('name');

        if ($user->can(Permission::SUB_ADMIN_MANAGEMENT->value)) {
            $salesUsersQuery->underSubAdmin($user);
        }

        $salesUsers = $salesUsersQuery->get(['id', 'name', 'status'])->append('has_today_activity');

        $draftingUsers = User::whereDrafting()->orderBy('name')->get(['id', 'name', 'status']);
        $serviceUsers = User::whereService()->orderBy('name')->get(['id', 'name', 'status']);
        $subAdminUsers = User::whereSubAdmin()->orderBy('name')->get(['id', 'name', 'status']);
        $scamStatuses = ScamStatus::withExists('statusUpdateFields')
            ->with(['previousStatuses', 'nextStatuses'])->orderBy('title')->get();
        $scamTypes = ScamType::orderBy('title')->get(['id', 'title']);
        $states = State::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $firstDraftingStatus = $scamStatuses->where('type', ScamStatusType::DRAFTING)->sortBy('index')->first();
        $scamSources = ScamSource::all(['id', 'title']);

        $hasFrozenStatus = $userRole &&
            (
                ScamStatus::where('is_freezable', true)->exists() ||
                setting("freeze_{$userRole}_null_threshold", null) !== null
            ) &&
            $this->service->hasFrozenStatus($user, $userRole);

        $this->activityLogService->visited('sub-admin scams list');

        ScamFilter::setData();

        $reminderScams = $this->service->statusReminderScams(Auth::user());

        return view('admin.scams.sub_admin', compact(
            'salesUsers',
            'scamStatuses',
            'draftingUsers',
            'serviceUsers',
            'subAdminUsers',
            'firstDraftingStatus',
            'scamTypes',
            'scamSources',
            'hasFrozenStatus',
            'reminderScams',
            'states'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->activityLogService->visited('create scam');

        $scamTypes = ScamType::all();

        return view('admin.scams.create', compact('scamTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScamRequest $request): JsonResponse
    {
        $scam = $this->service->create($request);

        $this->activityLogService->created('scam', $scam);

        $this->flashToast('success', 'Scam Created!');

        return $this->responseService->json(success: true, redirectTo: route('admin.scams.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Scam $scam): JsonResponse
    {
        $scam->load([
            'customer',
            'scamType:id,title',
            'salesAssignee:id,name,username',
            'draftingAssignee:id,name,username',
            'serviceAssignee:id,name,username',
            'salesStatus:id,title',
            'draftingStatus:id,title',
            'scamFiles',
            'scamFiles.file',
            'scamFiles.user:id,name,username',
            'scamStatusFiles',
            'scamStatusFiles.status:id,title',
            'scamStatusFiles.file',
            'scamStatusFiles.file.user:id,name,username',
            'salesStatusRecord',
            'draftingStatusRecord',
            'salesStatusRecord.causer:id,name,username',
            'draftingStatusRecord.causer:id,name,username',
            'statusRecords' => function (HasMany $q) {
                $q->orderBy('created_at', 'ASC')->orderBy('id', 'ASC');
            },
            'statusRecords.status:id,title',
            'statusRecords.causer:id,name,username',
            'activities' => function (HasMany $q) {
                $q->select(['id', 'scam_id', 'user_id', 'description', 'created_at'])
                    ->orderBy('created_at', 'ASC')->orderBy('id', 'ASC');
            },
            'activities.user:id,name,username,avatar,profile_picture_id',
        ]);

        $customer = $scam->customer;

        $this->activityLogService->visited('scam detail', $scam);

        if ($request->ajax()) {

            $html = view(
                view: 'admin.scams.detail-page.index',
                data: compact('scam', 'customer')
            )->render();

            return $this->responseService->json(success: true, html: $html);
        }

        return $this->responseService->json(success: true, data: $scam);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scam $scam): View
    {
        $this->activityLogService->visited('edit scam', $scam);

        $scam->load('scamSource');

        $scamTypes = ScamType::all();

        return view('admin.scams.edit', compact('scam', 'scamTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScamRequest $request, Scam $scam): JsonResponse
    {

        $updated = $this->service->update($scam, $request);

        if (! $updated) {
            return $this->responseService->json(success: false, toast: ['type' => 'warning', 'message' => 'No Changes Made!']);
        }

        $this->activityLogService->updated('scam', $scam);

        $this->flashToast('success', 'Scam Updated!');

        return $this->responseService->json(success: true, redirectTo: route('admin.scams.index'));
    }

    /**
     * Quick remark update for assignees or permissioned users.
     */
    public function updateRemark(Request $request, Scam $scam): JsonResponse
    {
        $user = $request->user();

        // Allow if user can perform full update or is associated with the scam (assignee)
        if (! ($user->can(Permission::SCAM_UPDATE->value) || $scam->isUserAssociated($user))) {
            abort(403, 'Unauthorized Access!');
        }

        $request->validate([
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        $scam->remark = $request->input('remark');
        $scam->save();

        $this->activityLogService->updated('scam remark', $scam);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Remark Saved!'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scam $scam): JsonResponse
    {
        if ($this->service->isDeletable($scam) && $this->service->delete($scam)) {
            $toast = ['type' => 'success', 'message' => 'Scam deleted!'];
            $this->activityLogService->deleted('scam', $scam);
        } else {
            $toast = ['type' => 'warning', 'message' => 'Scam can\'t be removed.'];
        }

        return $this->responseService->json(success: true, toast: $toast);
    }

    /**
     * Assign user to the scam
     */
    public function assignUser(AssignUserToScamRequest $request, Scam $scam): JsonResponse
    {
        $updated = $this->service->assignUser($scam, $request);

        if (! $updated) {
            // nothing was changed (permission denied or value identical)
            return $this->responseService->json(
                success: false,
                toast: new Toast('warning', 'Assignment was not saved.')
            );
        }

        $this->activityLogService->scamAssign($scam, $request->type, $request->assignee_id);

        return $this->responseService->json(success: true);
    }

    /**
     * Change the state of the customer belonging to the scam
     */
    public function changeState(Request $request, Scam $scam): JsonResponse
    {
        $request->validate([
            'state_id' => ['nullable', 'exists:states,id'],
        ]);

        $scam->load('customer');
        if($scam->customer) {
            $scam->customer->state = $request->state_id;
            $scam->customer->save();

            $this->activityLogService->updated('customer state', $scam); // or similar
            return $this->responseService->json(success: true);
        }

        return $this->responseService->json(success: false, toast: ['type' => 'error', 'message' => 'No Customer Found!']);
    }

    /**
     * Assign sales user and manage sales status of the scam
     */
    public function changeStatus(ChangeScamStatusRequest $request, Scam $scam, ChangeStatus $changeStatus): JsonResponse
    {
        $status = ScamStatus::where('id', $request->safe()->integer('status_id'))->first();

        if ($this->service->isStatusCapped($scam, $status)) {
            return $this->responseService->json(success: false, toast: new Toast('warning', 'Cap limit reached for this status!'));
        }

        $success = $changeStatus->handle($scam, $request);

        $this->activityLogService->updated('scam status', $scam, [
            'scam_id' => $scam->id,
            'status_id' => $request->status_id,
            'type' => $request->type,
        ]);

        Log::info('Scam Status Changed', [
            'scam_id' => $scam->id,
            'status_id' => $request->status_id,
            'type' => $request->type,
        ]);
        return $this->responseService->json(success: $success);
    }

    /**
     * Select Search for scams
     */
    public function selectSearch(Request $request): JsonResponse
    {
        $results = $this->service->selectSearch($request);

        return $this->responseService->json(success: true, data: $results);
    }

    /**
     * Get all escalations related to the scam
     */
    public function allScamEscalations(Request $request, Scam $scam): JsonResponse
    {
        $user = $request->user();

        $c1 = $user?->can(Permission::ESCALATION_LIST->value);
        $c2 = $user && $user->can(Permission::ESCALATION_LIST_SELF->value) && $scam->isUserAssociated($user);

        abort_if(! ($c1 || $c2), 403, 'Unauthorized Access!');

        $escalations = $scam->escalations;

        $escalations->each(function (Escalation &$escalation) {
            $escalation->append(['type_label', 'status_label', 'status_color']);
            $escalation->created_at_formatted = format_date($escalation->created_at);
        });

        $this->activityLogService->visited('scam escalation list', $scam);

        return $this->responseService->json(success: true, data: $escalations);
    }

    public function bulkAssignUsers(BulkAssignUserToScamRequest $request, BulkAssignUsers $bulkAssignUsers): JsonResponse
    {
        $updated = $bulkAssignUsers->handle($request);
        if ($updated) {
            $this->activityLogService->log(description: 'Bulk assign and update scams', event: ActivityEvent::SCAM_BULK_ASSIGN_AND_UPDATE, properties: $request->validated());
        }

        return $this->responseService->json(success: true, toast: ['type' => 'success', 'message' => 'Bulk assignment successful!']);
    }

    // public function processImportFile(ScamImportFileScanRequest $request): JsonResponse
    // {
    //     try {

    //         $this->uploadedFileService->uploadFromRequest($request, 'file', FileDirectory::SCAM_IMPORT_FILES);

    //         $data = $this->service->scanScamSheet($request);

    //         return $this->responseService->json(
    //             success: true,
    //             data: $data,
    //             toast: new Toast('success', 'File has been processed!')
    //         );

    //     } catch (ExcelFileValidationException $e) {

    //         return $this->responseService->json(success: false, toast: new Toast('error', $e->getMessage()));

    //     } catch (\Exception $e) {

    //         throw $e;
    //     }
    // }

    public function import(ScamFileImportRequest $request, ImportScamRecords $action): JsonResponse
    {
        $action->handle($request->data);

        $this->activityLogService->log(description: 'Imported Scam through sheet', event: ActivityEvent::SCAM_IMPORT);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Records Imported!'));
    }

    public function uploadScamFiles(UploadScamFilesRequest $request, Scam $scam): JsonResponse
    {
        $uploadedFiles = $this->uploadedFileService->uploadMultipleFromRequest(request: $request, fieldName: 'files', directory: FileDirectory::SCAM_FILES);

        $this->service->createScamFile($scam, $request, $uploadedFiles);

        $this->activityLogService->uploaded('scam file', $scam);

        return $this->responseService->json(success: true, toast: new Toast('success', 'Media uploaded!'));
    }

    public function changeScamStatusReview(ChangeScamStatusReviewRequest $request, Scam $scam, ChangeScamStatusReview $action): JsonResponse
    {
        $action->handle($scam, $request);

        $this->activityLogService->log(description: 'Changed Scam Status Review', event: ActivityEvent::SCAM_STATUS_REVIEW, properties: $request->validated());

        return $this->responseService->json(success: true, toast: new Toast('success', 'Review Resolved!'));
    }

    public function bulkRecycle(BulkRecycleScamRequest $request, BulkRecycleScam $action): JsonResponse
    {
        $action->handle($request);

        $this->activityLogService->log(description: 'Bulk recycle scams', event: ActivityEvent::SCAM_BULK_RECYCLE, properties: $request->validated());

        return $this->responseService->json(success: true, toast: new Toast('success', 'Case(s) Recycled'));
    }

    public function bulkUpdate(BulkUpdateScamRequest $request): JsonResponse
    {
        $this->service->bulkUpdate($request);

        $this->activityLogService->log(description: 'Bulk update scam details', event: ActivityEvent::SCAM_BULK_UPDATE, properties: $request->validated());

        return $this->responseService->json(success: true, toast: new Toast('success', 'Bulk Updation Complete!'));
    }

    public function statusDataForm(Scam $scam, ScamStatus $scamStatus): JsonResponse
    {
        if ($scamStatus->statusUpdateFields->isEmpty()) {
            return $this->responseService->json(success: false, message: 'No update fields available for this status.', statusCode: 400);
        }

        $html = view('admin.scams.ajax.status_update_data_form', compact('scam', 'scamStatus'))->render();

        $values = [
            'registrations' => $scam->registrations()->with('scamRegistrationAmount:id,title')->get(['id', 'scam_registration_amount_id']),
        ];

        return $this->responseService->json(success: true, html: $html, data: [
            'values' => $values,
        ]);
    }

    public function updateStatusData(UpdateStatusDataRequest $request, Scam $scam, ScamStatus $scamStatus, UpdateStatusWithData $action): JsonResponse
    {
        if ($this->service->isStatusCapped($scam, $scamStatus)) {
            return $this->responseService->json(success: false, toast: new Toast('warning', 'Cap limit reached for this status!'));
        }

        $action->handle($request);

        $this->activityLogService->updated('scam status', $scam, [
            'scam_id' => $scam->id,
            'status_id' => $scamStatus->id,
            'type' => $scamStatus->type,
        ]);

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message : 'Status Updated!'));
    }

    public function randomScamAssign(RandomScamAssignRequest $request, RandomScamAssign $action): JsonResponse
    {
        if (! is_office_time()) {
            return $this->responseService->json(success: true, toast: new Toast(type: 'error', message: 'This feature is only for office time!'));
        }

        $action->handle($request);

        $this->activityLogService->log(description: 'Random Scam Assign', event: ActivityEvent::RANDOM_SCAM_ASSIGN, properties: $request->all());

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message: 'Assign Complete!'));
    }

    public function deleteStatusFile(ScamStatusFile $scamStatusFile): JsonResponse
    {
        $scamStatusFile->delete();

        $this->activityLogService->log(description: 'Scam statsus file deleted', event: ActivityEvent::DELETED, entity: $scamStatusFile);

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message: 'Status File Deleted!'));
    }

    public function deleteScamFile(ScamFile $scamFile): JsonResponse
    {
        $scamFile->delete();

        $this->activityLogService->log(description: 'Scam file deleted', event: ActivityEvent::DELETED, entity: $scamFile);

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message: 'File Deleted!'));
    }

    public function acknowledgeStatusReminders(): JsonResponse
    {
        $this->service->acknowledgeStatusReminders(Auth::user());

        return $this->responseService->json(success: true, toast: new Toast(type: 'success', message: 'Reminder Acknowledged!'));
    }

    public function bulkExcelExport(Request $request)
{
    $exportType = $request->export_type;

    $query = Scam::with([
        'scamType',
        'scamSource',
        'salesAssignee',
        'draftingAssignee',
        'serviceAssignee',
        'salesStatus',
        'draftingStatus',
    ]);

    if ($exportType === 'selected') {
        $ids = explode(',', $request->scam_ids);
        $query->whereIn('id', $ids);
    } else {        $query = Scam::filter($request->all());
        $query = Scam::filter($request->all());
    }

    return Excel::download(
        new ScamExport($query->get()),
        'scams_' . now()->format('Ymd_His') . '.xlsx'
    );
}
}