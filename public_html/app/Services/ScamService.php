<?php

namespace App\Services;

use App\Constants\Permission;
use App\Enums\CustomerEnquiryStatusType;
use App\Enums\ScamActivityEvent;
use App\Enums\ScamAssigneeType;
use App\Enums\ScamStatusType;
use App\Filters\ScamFilter;
use App\Http\Requests\Admin\AssignUserToScamRequest;
use App\Http\Requests\Admin\BulkUpdateScamRequest;
use App\Http\Requests\Admin\ScamImportFileScanRequest;
use App\Http\Requests\Admin\ScamRequest;
use App\Http\Requests\Admin\UploadScamFilesRequest;
use App\Imports\ScamsImport;
use App\Jobs\Whatsapp\SendAssignMessageToCustomer;
use App\Models\Customer;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryStatus;
use App\Models\Scam;
use App\Models\ScamAssigneeRecord;
use App\Models\ScamStatus;
use App\Models\ScamStatusRecord;
use App\Models\ScamType;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\UserScamStatusFreeze;
use App\Notifications\CaseAssignedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\EloquentDataTable;

class ScamService extends Service
{
    public const ALLOWED_FILE_EXTENSIONS_FOR_IMPORT = ['xlsx', 'csv'];

    public function dataTable(Request $request): EloquentDataTable
    {
        $query = $this->getRequestTableQuery($request);

        $table = datatables()->eloquent($query);

        $table->addColumn('customer_info', fn (Scam $scam): array => [$scam->customer->fullName, $scam->customer->fullPhoneNumber]);

        $table->addColumn('formatted_scam_amount', fn (Scam $scam): ?string => $scam->scam_amount ? format_amount($scam->scam_amount) : null);

        $table->editColumn('created_at', fn (Scam $scam): ?string => format_date($scam->created_at));
        $table->editColumn('sales_assigned_at', fn (Scam $scam): ?string => format_date($scam->sales_assigned_at));
        $table->editColumn('drafting_assigned_at', fn (Scam $scam): ?string => format_date($scam->drafting_assigned_at));
        $table->editColumn('remark', fn (Scam $scam): ?string => $scam->remark);
        $table->filterColumn('customer_info', function (Builder $query, string $keyword) {
            $query->whereHas('customer', function (Builder $query) use ($keyword) {
                $query->whereSearch($keyword);
            });
        });

        $table->editColumn('customer', function (Scam $scam): ?array {
            $scam->customer->append('full_name_with_full_phone_number');

            return $scam->customer?->toArray();
        });

        $table->orderColumn('customer_info', function (Builder $query, string $order) {
            $query->join('customers', 'scams.customer_id', '=', 'customers.id')
                ->orderBy('customers.first_name', $order)
                ->orderBy('customers.last_name', $order);
        });

        $table->addColumn('sales_status_review_color', fn (Scam $scam) => $scam->salesStatusRecord?->review_color_faded);
        $table->addColumn('drafting_status_review_color', fn (Scam $scam) => $scam->draftingStatusRecord?->review_color_faded);

        $table->addColumn('registered_amount', fn (Scam $scam) => $scam->registered_amount);
        $table->addColumn('formatted_registered_amount', fn (Scam $scam) => $scam->formatted_registered_amount);

        return $table;
    }

    public function getRequestTableQuery(Request $request): Builder
    {
        $query = Scam::query();

        $user = $request->user();
        $userRole = $user->getRoleString();

        $recordsType = $request->integer('records_type');

        $query->with([
            'scamSource:id,slug,title',
            'salesStatusRecord',
            'draftingStatusRecord',
            'registrations.scamRegistrationAmount',
            'subAdmin:id,name',
        ]);

        $query->with([
            'customer:id,first_name,last_name,dial_code,phone_number,email',
        ]);

        if ($recordsType === 3 && $user->can(Permission::STATUS_UNASSIGNED_SCAM_LIST)) {
            $query->with([
                'latestSalesStatusUnassignRecord:id,assignee_id',
                'latestDraftingStatusUnassignRecord:id,assignee_id',
                'latestSalesStatusUnassignRecord.assignee:id,name',
                'latestDraftingStatusUnassignRecord.assignee:id,name',
            ]);
        }

        $query->leftJoin('scam_types', 'scams.scam_type_id', '=', 'scam_types.id');

        $query->select([
            'scams.*',
            'scam_types.title as scam_type',
        ]);

        // sales permission check
        if ($user->can(Permission::SALES_MANAGEMENT)) {
            // all access
        } elseif ($user->can(Permission::SALES_MANAGEMENT_SELF)) {
            $query->where('sales_assignee_id', $user->id);
        }

        // drafting permission check
        if ($user->can(Permission::DRAFTING_MANAGEMENT)) {
            // all access
        } elseif ($user->can(Permission::DRAFTING_MANAGEMENT_SELF)) {
            $query->where('drafting_assignee_id', $user->id);
        }

        // service permission check
        if ($user->can(Permission::SERVICE_MANAGEMENT)) {
            // all access
        } elseif ($user->can(Permission::SERVICE_MANAGEMENT_SELF)) {
            $query->where('service_assignee_id', $user->id);
        }

        // sub-admin permission check
        if ($request->routeIs('admin.sub_admin')) {
            if ($user->can(Permission::SUB_ADMIN_MANAGEMENT->value)) {
                $query->where('sub_admin_id', $user->id);
            }
        }

        if ($userRole && ! $user->isFreezeForceReleased()) {

            $freezes = UserScamStatusFreeze::whereUser($user, $userRole)->with('status:id,is_freezable,hours_to_freeze,freeze_release_scams_threshold')->get();

            if (
                (
                    ScamStatus::where('is_freezable', true)->count() > 0 ||
                    setting("freeze_{$userRole}_null_threshold", null) !== null
                ) &&
                $userRole &&
                in_array($userRole, ['sales', 'drafting']) &&
                $freezes->isNotEmpty()
            ) {
                $query->whereStatusFreezed($user, $freezes);
            }
        }

        // Filters
        ScamFilter::apply($query);

        return $query;
    }

    public function create(ScamRequest $request): Scam
    {
        $scam = new Scam($request->validated());
        $scam->save();

        return $scam;
    }

    public function update(Scam $scam, ScamRequest $request): bool
    {
        return DB::transaction(function () use ($scam, $request): bool {
            $scam->fill($request->validated());
            $scam->setAttribute('scam_source_id', $request->validated('scam_source_id', null));
            $this->logScamActivityBeforeUpdate($scam);

            return $scam->save();
        });
    }

    public function logScamActivityBeforeUpdate(Scam $scam): void
    {
        if ($scam->isDirty('scam_amount')) {
            $originalScamAmount = $scam->getOriginal('scam_amount');
            $newScamAmount = $scam->getAttribute('scam_amount');
            if ($originalScamAmount === null && $newScamAmount !== null) {
                $scamAmountFormatted = format_amount($newScamAmount);
                $scam->logActivity("Added scam amount : {$scamAmountFormatted}", ScamActivityEvent::UPDATED);
            } elseif ($newScamAmount === null && $originalScamAmount !== null) {
                $scam->logActivity('Removed scam amount', ScamActivityEvent::UPDATED);
            } else {
                $newScamAmountFormatted = format_amount($newScamAmount);
                $scam->logActivity("Scam amount updated : {$newScamAmountFormatted}", ScamActivityEvent::UPDATED);
            }
        }

        if ($scam->isDirty('scam_type_id')) {
            $originalScamTypeId = $scam->getOriginal('scam_type_id');
            $newScamTypeId = $scam->getAttribute('scam_type_id');
            $newScamTypeTitle = ScamType::find($newScamTypeId, ['title'])->title;
            if ($originalScamTypeId === null) {
                $scam->logActivity("Set scam type : {$newScamTypeTitle}", ScamActivityEvent::UPDATED);
            } else {
                $scam->logActivity("changed scam type : {$newScamTypeTitle}", ScamActivityEvent::UPDATED);
            }
        }

        $types = ['sales', 'drafting', 'service'];

        foreach ($types as $type) {
            if ($scam->isDirty("{$type}_assignee_id")) {
                $assigneeId = $scam->getAttribute("{$type}_assignee_id");
                if ($assigneeId) {
                    $assginee = User::find($assigneeId, ['name', 'username']);
                    $scam->logActivity(
                        "Assigned to {$type} member : {$assginee->name_with_username}",
                        ScamActivityEvent::from("{$type}_assign")
                    );
                } else {
                    $scam->logActivity(
                        "Removed {$type} assignee",
                        ScamActivityEvent::from("{$type}_assign")
                    );
                }
            }

            if ($type !== 'service') {
                if ($scam->isDirty("{$type}_status_id")) {
                    $statusId = $scam->getAttribute("{$type}_status_id");
                    if ($statusId) {
                        $status = ScamStatus::find($statusId, ['title']);
                        $scam->logActivity(
                            ucfirst($type) . " status updated : {$status->title}",
                            ScamActivityEvent::from("{$type}_status")
                        );
                    } else {
                        $scam->logActivity(
                            "Removed {$type} status",
                            ScamActivityEvent::from("{$type}_status")
                        );
                    }
                }
            }
            
            if ($scam->isDirty("sub_admin_id")) {
                $assigneeId = $scam->getAttribute("sub_admin_id");
                if ($assigneeId) {
                    $assginee = User::find($assigneeId, ['name', 'username']);
                    $scam->logActivity(
                        "Assigned to sub admin member : {$assginee->name_with_username}",
                        ScamActivityEvent::UPDATED
                    );
                } else {
                    $scam->logActivity(
                        "Removed sub admin assignee",
                        ScamActivityEvent::UPDATED
                    );
                }
            }
        }

    }

    public function isDeletable(Scam $scam): bool
    {
        return true;
    }

    public function delete(Scam $scam): ?bool
    {
        return DB::transaction(function () use ($scam): bool|null {
            $scam->scamFiles()->delete();

            return $scam->delete();
        });
    }

    public function assignUser(Scam $scam, AssignUserToScamRequest $request): bool
    {
        return DB::transaction(function () use ($scam, $request): bool {
            $user = $request->user();
            $data = $request->validated();

            $type = $data['type']; // sales, drafting, service, sub_admin
            $assigneeId = $data['assignee_id'];
            $enquiryId = $data['enquiry_id'] ?? null;

            // when assigning users we previously required a matching
            // "*_MANAGEMENT" permission. the frontend only enables the
            // sub-admin dropdown for sales managers, however sub-admins
            // also need to be able to assign themselves or others. keep
            // the original check but also allow sales managers when the
            // type is sub_admin so the UI and backend are in sync.
            $permission = constant(Permission::class . '::' . strtoupper($type) . '_MANAGEMENT');  // XYZ_MANAGEMENT

            $canAssign = $user->can($permission->value);
            if (! $canAssign && $type === 'sub_admin') {
                // sales managers may update the sub_admin_id even though
                // they don't own the SUB_ADMIN_MANAGEMENT permission
                $canAssign = $user->can(Permission::SALES_MANAGEMENT->value);
            }

            if ($canAssign) {
                $column = $type === 'sub_admin' ? 'sub_admin_id' : "{$type}_assignee_id";

                $scam->fill([
                    $column => $assigneeId,
                ]);

                if ($type === 'sub_admin' && $assigneeId) {

                    $scam->fill([
                        'sales_assignee_id' => null,
                        'sales_status_id' => null,
                    ]);
                }

                if ($scam->isDirty($column) || ($type === 'sub_admin' && $assigneeId && ($scam->isDirty('sales_assignee_id') || $scam->isDirty('sales_status_id')))) {

                    if ($scam->isDirty($column)) {
                        $scam->{"{$type}_assigned_at"} = now();
                    }

                    $this->logScamActivityBeforeUpdate($scam);

                    $scam->update();

                    if ($enquiryId) {
                        CustomerEnquiry::where('id', $enquiryId)->update(['manually_assigned_at' => now()]);
                    }

                    $assignee = User::find($assigneeId, 'id');

                    if($assignee) {
                        Notification::sendNow($assignee, new CaseAssignedNotification($scam));
                        \Log::info("Notification sent to user ID: {$assignee->id} for Scam ID: {$scam->id}");
                    }

                    // assignment happened
                    return true;
                }
            }

            return false;
        });
    }

    public function selectSearch(Request $request): Collection
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Scam::query()
            ->with([
                'customer:id,first_name,last_name,email,dial_code,phone_number',
                'scamType:id,title',
            ])
            ->whereSearch($search);

        $scams = $query->paginate($perPage, ['id', 'track_id', 'customer_id', 'scam_type_id', 'scam_amount'], 'page', $page);

        // format_amount

        return collect([
            'records' => $scams->map(function (Scam $scam) {

                $formattedAmount = $scam->scam_amount ? format_amount($scam->scam_amount) : null;

                $text = "#{$scam->track_id} - {$scam->customer?->fullNameWithFullPhoneNumber} - {$scam->scamType?->title}";

                if ($formattedAmount) {
                    $text .= " - {$formattedAmount}";
                }

                return [
                    'id' => $scam->id,
                    'text' => $text,
                ];
            }),
            'has_more_pages' => $scams->hasMorePages(),
        ]);
    }

    public function validateAssigneeId(int $assigneeId, string $type): bool
    {
        if (! in_array($type, ['sales', 'drafting', 'service', 'sub_admin'])) {
            throw new \InvalidArgumentException('Invalid Assignee type provided!');
        }

        // Map the required permissions based on the type.
        $requiredPermissions = match ($type) {
            'sales' => [
                Permission::SALES_MANAGEMENT->value,
                Permission::SALES_MANAGEMENT_SELF->value,
            ],
            'drafting' => [
                Permission::DRAFTING_MANAGEMENT->value,
                Permission::DRAFTING_MANAGEMENT_SELF->value,
            ],
            'service' => [
                Permission::SERVICE_MANAGEMENT->value,
                Permission::SERVICE_MANAGEMENT_SELF->value,
            ],
            'sub_admin' => [
                Permission::SUB_ADMIN_MANAGEMENT->value,
            ],
            default => [],
        };

        // Check if the user has at least one of the required permissions.
        return User::whereId($assigneeId)
            ->where('status', true)
            ->whereHas('roles.permissions', function ($query) use ($requiredPermissions) {
                $query->whereIn('name', $requiredPermissions);
            })
            ->exists();
    }

    // public function scanScamSheet(ScamImportFileScanRequest $request): array
    // {
    //     $file = $request->file('file');

    //     $import = new ScamsImport;
    //     Excel::import($import, $file);

    //     if ((bool) $request->input('unique_phone_number', false)) {
    //         $import->uniqueMobileNumber();
    //     }
    //     if ((bool) $request->input('unique_scam_type', false)) {
    //         $import->uniqueScamType();
    //     }
    //     if ((bool) $request->input('unique_scam_amount', false)) {
    //         $import->uniqueScamAmount();
    //     }
    //     if ((bool) $request->input('skip_existing_phone_number', false)) {
    //         $import->skipExistingPhoneNumber();
    //     }

    //     return $import->getProcessedData();
    // }

    public function createScamFile(Scam $scam, UploadScamFilesRequest $request, UploadedFile|array $uploadedFiles): bool
    {
        return DB::transaction(function () use ($scam, $request, $uploadedFiles): bool {

            if (! is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }

            $messages = $request->validated('messages', []);

            $i = 0;

            foreach ($uploadedFiles as $uploadedFile) {

                $scam->scamFiles()->create([
                    'file_id' => $uploadedFile->id,
                    'message' => $messages[$i] ?? null,
                    'user_id' => $request->user()->id,
                ]);

                $i++;
            }

            return true;

        });
    }

    // Modal Event Handlers

    public function handleScamStatusUpdateEvent(Scam $scam, bool $save = false): void
    {

        if ($scam->isDirty('sales_status_id') && $scam->sales_status_id) {
            $scam->sales_status_updated_at = now();
        }

        if ($scam->isDirty('drafting_status_id') && $scam->drafting_status_id) {
            $scam->drafting_status_updated_at = now();
        }

        if ($save) {
            $scam->saveQuietly();
        }
    }

    public function handleScamStatusUpdatedEvent(Scam $scam, bool $save = false): void
    {
        $authId = Auth::id();

        if ($scam->isDirty('sales_status_id')) {

            $scam->sales_status_record_id = ScamStatusRecord::logRecord(scam: $scam, type: ScamStatusType::SALES, causer: $authId)?->id;

            UserScamStatusFreeze::checkAndTryFreezeRelease(scam: $scam, statusId: $scam->getOriginal('sales_status_id'), scamStatusType: ScamStatusType::SALES);

            $this->unassignCaseOnStatusChange($scam, ScamStatusType::SALES);
        }

        if ($scam->isDirty('drafting_status_id')) {

            $scam->drafting_status_record_id = ScamStatusRecord::logRecord(scam: $scam, type: ScamStatusType::DRAFTING, causer: $authId)?->id;

            UserScamStatusFreeze::checkAndTryFreezeRelease(scam: $scam, statusId: $scam->getOriginal('drafting_status_id'), scamStatusType: ScamStatusType::DRAFTING);

            $enquiryResolvedStatus = CustomerEnquiryStatus::where('type', CustomerEnquiryStatusType::DRAFTING)
                ->where('consider_resolved', true)
                ->first(['id']);

            if ($enquiryResolvedStatus) {
                $scam->customer->enquiries()->where('occurrence', '>', 0)->first()?->update(['drafting_status_id' => $enquiryResolvedStatus->id]);
            }

            $this->unassignCaseOnStatusChange($scam, ScamStatusType::DRAFTING);
        }

        if ($save) {
            $scam->saveQuietly();
        }
    }

    public function handleScamAssigneeUpdateEvent(Scam $scam, bool $save = false): void
    {
        if ($scam->isDirty('sales_assignee_id')) {
            if ($scam->sales_assignee_id) {
                $scam->sales_assigned_at = now();
            }
        }

        if ($scam->isDirty('drafting_assignee_id')) {
            if ($scam->drafting_assignee_id) {
                $scam->drafting_assigned_at = now();
            }
        }

        if ($scam->isDirty('service_assignee_id')) {
            if ($scam->service_assignee_id) {
                $scam->service_assigned_at = now();
            }
        }

        if ($scam->isDirty('sub_admin_id')) {
            if ($scam->sub_admin_id) {
                $scam->sub_admin_assigned_at = now();
            }
        }

        if ($save) {
            $scam->saveQuietly();
        }
    }

    public function handleScamAssigneeUpdatedEvent(Scam $scam, ?ScamStatus $unassignStatus = null, bool $save = false): void
    {
        $authId = Auth::id();

        if ($scam->isDirty('sales_assignee_id')) {
            if ($scam->sales_assignee_id) {
                // SendAssignMessageToCustomer::dispatch($scam->id, ScamAssigneeType::SALES->value);

                $scam->latest_sales_status_unassign_record_id = null;
            }
        }

        if ($scam->isDirty('drafting_assignee_id')) {
            if ($scam->drafting_assignee_id) {
                // SendAssignMessageToCustomer::dispatch($scam->id, ScamAssigneeType::DRAFTING->value);

                $scam->latest_drafting_status_unassign_record_id = null;
            }
        }

        if ($scam->isDirty('service_assignee_id')) {
            if ($scam->service_assignee_id) {
                $scam->latest_service_status_unassign_record_id = null;
            }
        }

        if ($scam->isDirty('sales_assignee_id') || ($unassignStatus && $unassignStatus->type === ScamStatusType::SALES)) {
            ScamAssigneeRecord::logRecord(scam: $scam, type: ScamAssigneeType::SALES, causer: $authId, unassignStatus: $unassignStatus);
        }

        if ($scam->isDirty('drafting_assignee_id') || ($unassignStatus && $unassignStatus->type === ScamStatusType::DRAFTING)) {
            ScamAssigneeRecord::logRecord(scam: $scam, type: ScamAssigneeType::DRAFTING, causer: $authId, unassignStatus: $unassignStatus);
        }

        if ($scam->isDirty('service_assignee_id') || ($unassignStatus && $unassignStatus->type === ScamAssigneeType::SERVICE)) {
            ScamAssigneeRecord::logRecord(scam: $scam, type: ScamAssigneeType::SERVICE, causer: $authId, unassignStatus: $unassignStatus);
        }

        if ($scam->isDirty('sub_admin_id') || ($unassignStatus && $unassignStatus->type === ScamAssigneeType::SUB_ADMIN)) {
            ScamAssigneeRecord::logRecord(scam: $scam, type: ScamAssigneeType::SUB_ADMIN, causer: $authId, unassignStatus: $unassignStatus);
        }

        if ($save) {
            $scam->saveQuietly();
        }
    }

    public function resetScamStatus(Scam $scam, bool $save = false): void
    {

        if ($scam->isDirty('sales_assignee_id') && $scam->sales_assignee_id && $scam->sales_status_id) {
            $scam->sales_status_id = null;
            $scam->logActivity('Removed sales status', ScamActivityEvent::from('sales_status'));
        }

        if ($scam->isDirty('drafting_assignee_id') && $scam->drafting_assignee_id && $scam->drafting_status_id) {
            $scam->drafting_status_id = null;
            $scam->logActivity('Removed drafting status', ScamActivityEvent::from('drafting_status'));
        }

        if ($save) {
            $scam->save();
        }
    }

    public function unassignCaseOnStatusChange(Scam $scam, ScamStatusType $statusType): void
    {
        // Manually doing the event work... to prevent infinte recursion

        $status = $scam->{"{$statusType->value}Status"};
        $assigneeColumn = "{$statusType->value}_assignee_id";

        if ($status?->unassign_scam && ! $status->unassign_scam_in_days) {

            $originalAssigneeId = $scam->$assigneeColumn;

            $scam->fill([
                $assigneeColumn => null,
            ]);

            $scam->statusUnassignRecords()->create([
                'assignee_id' => $originalAssigneeId,
                'status_id' => $status->id,
                'status_type' => $status->type,
            ]);


            $scam->logActivity(
                "Removed {$statusType->value} assignee (Due to status update)",
                ScamActivityEvent::from("{$statusType->value}_assign")
            );

            $this->handleScamAssigneeUpdatedEvent(scam: $scam, unassignStatus: $status);

            $scam->saveQuietly();
        }
    }

    public function syncIsDuplicateAfterDelete(Scam $scam): void
    {
        // $scam here is deleted

        $scams = Scam::whereNot('id', $scam->id)->where('customer_id', $scam->customer_id)->get(['id', 'is_duplicate']);

        $count = $scams->count();

        if ($count === 1) {

            $firstScam = $scams->first();

            if ($firstScam->is_duplicate) {

                $firstScam->update(['is_duplicate' => false]);

            }

        } elseif ($count > 1) {

            $firstScam = $scams->first();

            $firstScam->update(['is_duplicate' => false]);

            // Update the rest of the scams to is_duplicate = 1
            $scams->skip(1)->each(function (Scam $scam) {

                $scam->update(['is_duplicate' => true]);

            });

        }

    }

    public function hasFrozenStatus(User $user, string $userRole): bool
    {
        return ! $user->isFreezeForceReleased() &&
            UserScamStatusFreeze::whereUser($user, $userRole)->exists();
    }

    public function bulkUpdate(BulkUpdateScamRequest $request)
    {
        DB::transaction(function () use ($request) {
            $scamIds = $request->validated('scams', []);

            $scams = Scam::whereIn('id', $scamIds)->get(['id', 'scam_amount', 'scam_type_id', 'scam_source_id']);

            foreach ($scams as $scam) {

                $data = $request->only('scam_amount', 'scam_type_id', 'scam_source_id');

                if (! $data['scam_type_id']) {
                    unset($data['scam_type_id']);
                }
                if (! $data['scam_source_id']) {
                    unset($data['scam_source_id']);
                }

                $scam->fill($data);

                $this->logScamActivityBeforeUpdate($scam);

                $scam->save();
            }

        });
    }

   public function getScamTitle(Scam $scam): string
    {
        $scam->load([
            'customer:id,first_name,last_name,dial_code,phone_number',
            'scamType:id,title',
        ]);

        $parts = [];

        if ($scam->customer) {
            $parts[] = $scam->customer->full_name;
        }

        if ($scam->scamType) {
            $parts[] = $scam->scamType->title;
        }

        if (! is_null($scamAmount = $scam->scam_amount)) {
            $parts[] = format_amount($scamAmount);
        }

        return implode(' | ', $parts);
    }

    public function isStatusCapped(Scam $scam, ?ScamStatus $status): bool
    {
        if (! $status?->cap_scams) {
            return false;
        }

        $type = $status->type->value;
        $cap = (int) $status->cap_scams;

        $authUser = Auth::user();

        if ($authUser->userType() === 'admin') {
            return false;
        }

        $assignee = $scam->{"{$type}Assignee"};

        if (! $assignee) {
            return false;
        }

        $scamCount = Scam::query()
            ->where('is_duplicate', false)
            ->where("{$type}_assignee_id", $assignee->id)
            ->where("{$type}_status_id", $status->id)
            ->when(
                $status->cap_last_days,
                fn ($q) => $q->where("{$type}_status_updated_at", '>=', now()->subDays($status->cap_last_days))
            )
            ->count();

        return $scamCount >= $cap;
    }

    public function statusReminderScams(User $assignee): Collection
    {
        $userType = $assignee->userType();

        if (! in_array($userType, ScamAssigneeType::array())) {
            return collect();
        }

        $query = Scam::query()
            ->with('customer:id,first_name,last_name,country_code,dial_code,phone_number')
            ->with("{$userType}Status:id,title")
            ->select(['id', 'track_id', 'customer_id', 'scam_amount', "{$userType}_status_id"])
            ->where('is_duplicate', false)
            ->where("{$userType}_assignee_id", $assignee->id)
            ->whereNotNull("{$userType}_status_id")
            ->whereHas("{$userType}StatusRecord", function (Builder $q) {
                $q->whereNotNull('status_notify_at')
                    ->whereNull('status_notification_acknowledged_at')
                    ->where('status_notify_at', '<=', now());
            });

        $scams = $query->get()
            ->map(function (Scam $scam) use ($userType) {
                $scam->status = $scam->{"{$userType}Status"};

                return $scam;
            });

        return $scams;
    }

    public function acknowledgeStatusReminders(User $assignee)
    {

        $userType = $assignee->userType();

        if (! in_array($userType, ScamAssigneeType::array())) {
            return;
        }

        $query = Scam::query()
            ->with("{$userType}StatusRecord:id")
            ->select(['id', "{$userType}_status_record_id"])
            ->where('is_duplicate', false)
            ->where("{$userType}_assignee_id", $assignee->id)
            ->whereNotNull("{$userType}_status_id")
            ->whereHas("{$userType}StatusRecord", function (Builder $q) {
                $q->whereNotNull('status_notify_at')
                    ->whereNull('status_notification_acknowledged_at')
                    ->where('status_notify_at', '<=', now());
            });

        $scams = $query->get();

        $now = now();

        foreach ($scams as $scam) {
            $scam->{"{$userType}StatusRecord"}?->update([
                'status_notification_acknowledged_at' => $now,
            ]);
        }

    }

    public function markUnecessaryFreshScamAsDuplicate(): void
    {
        $appRunningInConsole = app()->runningInConsole();

        $duplicateCustomerIds = Scam::query()
            ->select('customer_id')
            ->where('is_duplicate', false)
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('customer_id');

        // $customers = Customer::whereIn('id', $duplicateCustomerIds)->get(['country_code', 'phone_number']);
        // $csvData = [];
        // $csvData[] = ['country_code', 'phone_number']; // Header row
        // foreach ($customers as $customer) {
        //     $csvData[] = [$customer->country_code, $customer->phone_number];
        // }
        // $csvFilePath = base_path('phone_numbers.csv');
        // $handle = fopen($csvFilePath, 'w');
        // foreach ($csvData as $row) {
        //     fputcsv($handle, $row);
        // }
        // fclose($handle);
        // dd('csv saved!');

        $data = Scam::query()
            ->whereIn('customer_id', $duplicateCustomerIds)
            ->where('is_duplicate', false)
            // ->whereNull(['sales_status_id', 'drafting_status_id'])
            // ->whereDoesntHave('statusRecords')
            ->get(['id']);

        $now = now();
        $batchId = Str::uuid();
        $i = 1;

        foreach ($data as $scam) {
            $scam->update(['is_duplicate' => true]);
            DB::table('scam_event_tracker')->insert([
                'scam_id' => $scam->id,
                'event' => 'unecessary_fresh_scam_mark_duplicate',
                'batch_id' => $batchId,
                'created_at' => $now,
            ]);

            if ($appRunningInConsole) {
                dump($i);
            }
            $i++;
        }

    }
}
