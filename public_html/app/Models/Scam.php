<?php

namespace App\Models;

use App\Enums\ScamActivityEvent;
use App\Enums\ScamStatusType;
use App\Foundation\Model;
use App\Models\Scopes\NonRecycledScope;
use App\Services\ScamService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[ScopedBy(NonRecycledScope::class)]
class Scam extends Model
{
    use HasFactory;

    public const BASE_TRACK_NUMBER = 100000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'scam_type_id',
        'scam_amount',
        'customer_description',

        'sales_assignee_id',
        'sales_status_id',
        'sales_assigned_at',
        'sales_status_updated_at',

        'drafting_assignee_id',
        'drafting_status_id',
        'drafting_assigned_at',
        'drafting_status_updated_at',

        'service_assignee_id',
        'service_status_id',
        'service_assigned_at',

        'sales_status_record_id',
        'drafting_status_record_id',
        'service_status_record_id',

        'latest_sales_status_unassign_record_id',
        'latest_drafting_status_unassign_record_id',
        'latest_service_status_unassign_record_id',

        'recycled_parent_scam_id',
        'recycled_at',

        'is_duplicate',
        'scam_source_id',
        'remark',
        'sub_admin_id',
        'sub_admin_assigned_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'event' => ScamActivityEvent::class,
        'sales_assigned_at' => 'datetime',
        'drafting_assigned_at' => 'datetime',
        'service_assigned_at' => 'datetime',
        'sub_admin_assigned_at' => 'datetime',
        'recycled_at' => 'datetime',
        'is_duplicate' => 'boolean',
    ];

    /**
     * The "booted" method is called when the model is booted.
     */
    protected static function booted()
    {
        /**
         * After creating the scam record
         */
        static::created(function (Scam $scam): void {

            // After the record has been created, set the track_id
            $scam->track_id = Scam::BASE_TRACK_NUMBER + $scam->id;

            // check for duplicate
            if (Scam::otherScams($scam)->exists()) {
                $scam->is_duplicate = true;
            }

            if ($scam->saveQuietly()) {
                $scam->logActivity('Case created', ScamActivityEvent::CREATED);
            }

        });

        /**
         * Before saving the record (create/update)
         */
        static::saving(function (Scam $scam): void {

            ScamService::getInstance()->handleScamStatusUpdateEvent($scam);

            ScamService::getInstance()->handleScamAssigneeUpdateEvent($scam);

            ScamService::getInstance()->resetScamStatus($scam);

        });

        static::saved(function (Scam $scam) {

            if (request()->boolean('status_update_request')) {
                ScamService::getInstance()->handleScamStatusUpdatedEvent($scam);
            }

        });

        static::updated(function (Scam $scam): void {

            ScamService::getInstance()->handleScamStatusUpdatedEvent($scam);

            ScamService::getInstance()->handleScamAssigneeUpdatedEvent($scam);

            $scam->saveQuietly();

        });

        static::deleted(function (Scam $scam) {
            ScamService::getInstance()->syncIsDuplicateAfterDelete($scam);
        });

    }

    public static function scopeOtherScams(Builder $query, Scam $scam): void
    {
        $query->where('customer_id', $scam->customer_id)->whereNot('id', $scam->id);
    }

    public static function scopeWhereSearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search) {
            $query->where('track_id', 'LIKE', "%{$search}%")
                ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                    $customerQuery->whereSearch($search);
                });
        });
    }

    public static function scopeWhereNotDuplicate(Builder $query): void
    {
        $query->where('is_duplicate', false);
    }

    public static function scopeWhereFreshScams(Builder $query): void
    {
        $query->whereNull(['sales_assignee_id', 'drafting_assignee_id', 'service_assignee_id', 'sales_status_id', 'drafting_status_id', 'service_status_id'])
            ->whereDoesntHave('assigneeRecords')
            ->whereDoesntHave('statusRecords');
    }

    public static function scopeWhereStatusFreezed(Builder $query, User $user, UserScamStatusFreeze|Collection $freezes)
    {
        $query->where(function (Builder $q) use ($user, $freezes) {

            $userRole = $user->getRoleString();

            $statusColumn = "{$userRole}_status_id";
            $updatedAtColumn = "{$userRole}_status_updated_at";
            $assignedAtColumn = "{$userRole}_assigned_at";

            if (! is_iterable($freezes)) {
                $freezes = collect([$freezes]);
            }

            foreach ($freezes as $freeze) {
                $statusId = $freeze->status_id;
                $hours = optional($freeze->status)->hours_to_freeze;
                $isStatusFreezable = optional($freeze->status)->is_freezable;

                if ($isStatusFreezable && ! is_null($statusId) && ! is_null($hours)) {
                    $q->orWhere(function ($subQ) use ($statusColumn, $updatedAtColumn, $statusId, $hours) {
                        $subQ->where($statusColumn, $statusId)
                            ->where($updatedAtColumn, '<=', now()->subHours($hours));
                    });
                }

                if (is_null($statusId)) {
                    $nullFreezeThreshold = setting("freeze_{$userRole}_null_threshold");
                    $nullHoursToFreeze = setting("hours_to_freeze_{$userRole}_null");
                    if ($nullFreezeThreshold && $nullHoursToFreeze) {
                        $q->orWhere(function ($subQ) use ($assignedAtColumn, $userRole, $nullHoursToFreeze) {
                            $subQ->whereNull("{$userRole}_status_id")
                                ->where($assignedAtColumn, '<=', now()->subHours($nullHoursToFreeze));
                        });
                    }
                }
            }

        });
    }

    public function isUserAssociated(User $user): bool
    {
        return $this->sales_assignee_id == $user->id ||
            $this->drafting_assignee_id == $user->id ||
            $this->service_assignee_id == $user->id ||
            $this->sub_admin_id == $user->id;
    }

    public function logActivity(string $description, ScamActivityEvent $event, ?Carbon $notifyAt = null): ScamActivity
    {
        return $this->activities()->create([
            'description' => $description,
            'user_id' => request()->user()?->id,
            'event' => $event,
            'notify_at' => $notifyAt,
        ]);
    }

    public function getStatus(ScamStatusType $type, array|string $columns = ['*']): ?ScamStatus
    {
        return match ($type) {
            ScamStatusType::SALES => $this->salesStatus()->first($columns),
            ScamStatusType::DRAFTING => $this->draftingStatus()->first($columns),
        };
    }

    public function previousStatusRecord(ScamStatusType $type, array|string $columns = ['*']): ?ScamStatusRecord
    {
        return ScamStatusRecord::where('scam_id', $this->id)
            ->where('status_type', $type)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip(1)
            ->first($columns);
    }

    /**
     * Get the customer associated with the scam.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the type of the scam.
     */
    public function scamType(): BelongsTo
    {
        return $this->belongsTo(ScamType::class);
    }

    /**
     * Get the user who is the sales assignee for the scam.
     */
    public function salesAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_assignee_id');
    }

    /**
     * Get the sales status of the scam.
     */
    public function salesStatus(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class, 'sales_status_id');
    }

    /**
     * Get the user who is the drafting assignee for the scam.
     */
    public function draftingAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drafting_assignee_id');
    }

    /**
     * Get the drafting status of the scam.
     */
    public function draftingStatus(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class, 'drafting_status_id');
    }

    /**
     * Get the user who is the service assignee for the scam.
     */
    public function serviceAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_assignee_id');
    }

    /**
     * Get the user who is the sub-admin assigned to the scam.
     */
    public function subAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sub_admin_id');
    }

    /**
     * Get all the escalations of the scam
     */
    public function escalations(): HasMany
    {
        return $this->hasMany(Escalation::class);
    }

    /**
     * Get the status files of the scam
     */
    public function scamStatusFiles(): HasMany
    {
        return $this->hasMany(ScamStatusFile::class);
    }

    /**
     * Get the scam files of the scam
     */
    public function scamFiles(): HasMany
    {
        return $this->hasMany(ScamFile::class);
    }

    /**
     * Get the activities of the scam
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ScamActivity::class);
    }

    /**
     * Get the scam source of the scam
     */
    public function scamSource(): BelongsTo
    {
        return $this->belongsTo(ScamSource::class, 'scam_source_id');
    }

    /**
     * Get the assignee records of this scam
     */
    public function assigneeRecords(): HasMany
    {
        return $this->hasMany(ScamAssigneeRecord::class);
    }

    /**
     * Get the status records for this scam
     */
    public function statusRecords(): HasMany
    {
        return $this->hasMany(ScamStatusRecord::class);
    }

    /**
     * Get the statusRecord of sales status of this scam
     */
    public function salesStatusRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusRecord::class, 'sales_status_record_id');
    }

    /**
     * Get the statusRecord of drafting status of this scam
     */
    public function draftingStatusRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusRecord::class, 'drafting_status_record_id');
    }

    /**
     * Get the statusRecord of service status of this scam
     */
    public function serviceStatusRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusRecord::class, 'service_status_record_id');
    }

    /**
     * Get the auto assigned target records
     */
    public function autoAssignTargetRecords(): HasMany
    {
        return $this->hasMany(ScamAutoAssignScam::class, 'target_scam_id');
    }

    /**
     * Get the recycled parent of the scam
     */
    public function recycledParentScam(): BelongsTo
    {
        return $this->belongsTo(Scam::class, 'recycled_parent_scam_id')->withoutGlobalScope(NonRecycledScope::class);
    }

    /**
     * Get the ScamRegistration for this scam
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(ScamRegistration::class);
    }

    /**
     * Get the latest status unassign record for ths scam
     */
    public function statusUnassignRecords(): HasMany
    {
        return $this->hasMany(ScamStatusUnassignRecord::class);
    }

    /**
     * Get the latest sales status unassign record for ths scam
     */
    public function latestSalesStatusUnassignRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusUnassignRecord::class, 'latest_sales_status_unassign_record_id');
    }

    /**
     * Get the latest drafting status unassign record for ths scam
     */
    public function latestDraftingStatusUnassignRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusUnassignRecord::class, 'latest_drafting_status_unassign_record_id');
    }

    /**
     * Get the latest service status unassign record for ths scam
     */
    public function latestServiceStatusUnassignRecord(): BelongsTo
    {
        return $this->belongsTo(ScamStatusUnassignRecord::class, 'latest_service_status_unassign_record_id');
    }

    /**
     * Get total registered amount
     */
    public function getRegisteredAmountAttribute(): float|int
    {
        return (float) $this->registrations->sum(function ($registration) {
            return $registration->scamRegistrationAmount?->amount ?? 0;
        });
    }

    /**
     * Get formatted registered amount
     */
    public function getFormattedRegisteredAmountAttribute(): string
    {
        return format_amount($this->registered_amount);
    }
}
