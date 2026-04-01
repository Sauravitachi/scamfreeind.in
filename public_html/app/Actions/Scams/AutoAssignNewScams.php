<?php

namespace App\Actions\Scams;

use App\Enums\MissedAutoScamAssignResolutionStatus;
use App\Enums\ScamAssigneeType;
use App\Models\AutoScamAssignRecord;
use App\Models\MissedAutoScamAssignRecord;
use App\Models\Scam;
use App\Models\ScamAutoAssignScam;
use App\Models\User;
use App\Notifications\AutoScamAssignUserNotification;
use App\Notifications\MissedAutoScamAssignedNotification;
use App\Services\ScamService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class AutoAssignNewScams
{
    protected int $targetSalesStatusId = 6; // Registered

    protected ?int $thresholdCaseCount;

    protected ?int $achieveInHours;

    protected ?int $assignNewCases;

    protected ?string $freshCaseDateRange;

    protected array $missedAssignNotifyRoles;

    protected ?int $lbScamAmount;

    protected bool $allowNullScamAmount;

    public function __construct(
        protected ScamService $scamService
    ) {
        $settings = setting([
            'sales_auto_case_assign:threshold_case_count',
            'sales_auto_case_assign:achieve_in_hours',
            'sales_auto_case_assign:new_cases_count',
            'sales_auto_case_assign:missed_assign_notify_to_roles',
            'sales_auto_case_assign:fresh_cases_date_range',
            'sales_auto_case_assign:lb_scam_amount',
            'sales_auto_case_assign:allow_null_amount',
        ]);
        $this->thresholdCaseCount = $settings->get('sales_auto_case_assign:threshold_case_count')?->value;
        $this->achieveInHours = $settings->get('sales_auto_case_assign:achieve_in_hours')?->value ?? 0;
        $this->assignNewCases = $settings->get('sales_auto_case_assign:new_cases_count')?->value;
        $this->missedAssignNotifyRoles = json_decode($settings->get('sales_auto_case_assign:missed_assign_notify_to_roles')?->value ?? '[]');
        $this->freshCaseDateRange = $settings->get('sales_auto_case_assign:fresh_cases_date_range')?->value;
        $this->lbScamAmount = $settings->get('sales_auto_case_assign:lb_scam_amount')?->value;
        $this->allowNullScamAmount = (bool) $settings->get('sales_auto_case_assign:allow_null_amount')?->value;
    }

    public function handle(): void
    {
        if (! $this->thresholdCaseCount || ! $this->assignNewCases) {
            return;
        }

        DB::transaction(fn () => $this->processUsers());
    }

    protected function processUsers(): void
    {
        $this->getEligibleSalesUsers()->each(function (User $user) {
            // if ($this->alreadyAssignedToday($user)) {
            //     return;
            // }

            $targetScamIds = $this->hasAchievedTarget($user);
            if ($targetScamIds instanceof Collection) {
                $this->assignNewScamsToUser($user, $targetScamIds);
            }
        });
    }

    protected function getEligibleSalesUsers(): Collection
    {
        return User::whereSales()
            ->where('id', 21)
            ->whereHas('salesAssignedScams', fn (Builder $q) => $q->whereDate('sales_assigned_at', today()))
            ->get(['id']);
    }

    protected function alreadyAssignedToday(User $user): bool
    {
        return AutoScamAssignRecord::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();
    }

    protected function hasAchievedTarget(User $user): Collection|bool
    {
        $scamIds = Scam::whereDoesntHave('autoAssignTargetRecords')
            ->where('sales_assignee_id', $user->id)
            ->where('is_duplicate', false)
            ->where('sales_status_id', $this->targetSalesStatusId)
            ->whereDate('sales_status_updated_at', today())
            // ->whereRaw('TIMESTAMPDIFF(HOUR, sales_assigned_at, sales_status_updated_at) <= ?', [$this->achieveInHours])
            ->pluck('id');

        return $scamIds->count() >= $this->thresholdCaseCount ? $scamIds : false;
    }

    protected function assignNewScamsToUser(User $user, Collection $targetScamIds): void
    {
        $scamsQuery = Scam::whereFreshScams()->where('is_duplicate', false);

        if ($this->lbScamAmount !== null) {
            $scamsQuery->where(function ($q) {
                $q->where('scam_amount', '>=', $this->lbScamAmount);
                if ($this->allowNullScamAmount) {
                    $q->orWhereNull('scam_amount');
                }
            });
        }

        if (! empty($this->freshCaseDateRange) && $dateRange = carbon_date_range($this->freshCaseDateRange)) {
            $scamsQuery->whereBetween('created_at', $dateRange->array());
        }

        $scamsQuery->inRandomOrder()->take($this->assignNewCases);

        if ($scamsQuery->count() < $this->assignNewCases) {
            $missedAutoScamAssignRecord = $this->createMissedRecord($user);
            $this->notifyMissedRecord($missedAutoScamAssignRecord);

            return;
        }

        $scams = $scamsQuery->get();

        $batchId = Str::uuid();

        $scams->each(function (Scam $scam) use ($user, $batchId) {
            $this->assignScamToUser($scam, $user, $batchId);
        });

        $targetScamIds->each(function (int $scamId) use ($user, $batchId) {
            ScamAutoAssignScam::create([
                'user_id' => $user->id,
                'target_scam_id' => $scamId,
                'assigned_scams_batch_id' => $batchId,
            ]);
        });

        $this->markMissedRecordsAsAssigned($user);

        Notification::sendNow($user, new AutoScamAssignUserNotification($this->assignNewCases));
    }

    protected function assignScamToUser(Scam $scam, User $user, string $batchId): void
    {
        $scam->fill([
            'sales_assignee_id' => $user->id,
            'sales_assigned_at' => now(),
        ]);

        $this->scamService->logScamActivityBeforeUpdate($scam);
        $scam->update();

        AutoScamAssignRecord::create([
            'user_id' => $user->id,
            'scam_id' => $scam->id,
            'assignee_type' => ScamAssigneeType::SALES,
            'batch_id' => $batchId,
        ]);
    }

    protected function createMissedRecord(User $user): MissedAutoScamAssignRecord
    {
        return MissedAutoScamAssignRecord::create([
            'user_id' => $user->id,
            'achieve_in_hours' => $this->achieveInHours,
            'threshold_case_count' => $this->thresholdCaseCount,
            'new_cases_count' => $this->assignNewCases,
        ]);
    }

    protected function notifyMissedRecord(MissedAutoScamAssignRecord $missedAutoScamAssignRecord): void
    {
        $notifyUsers = User::whereHas('roles', fn (Builder $q) => $q->whereIn('id', $this->missedAssignNotifyRoles))->get('id');

        if ($notifyUsers->isNotEmpty()) {
            Notification::sendNow($notifyUsers, new MissedAutoScamAssignedNotification($missedAutoScamAssignRecord));
        }
    }

    protected function markMissedRecordsAsAssigned(User $user): void
    {
        MissedAutoScamAssignRecord::where('user_id', $user->id)
            ->where('resolution_status', MissedAutoScamAssignResolutionStatus::PENDING)
            ->whereDate('created_at', today())
            ->update(['resolution_status' => MissedAutoScamAssignResolutionStatus::ASSIGNED]);
    }
}
