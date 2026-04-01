<?php

namespace App\Actions\Scams;

use App\Enums\ScamActivityEvent;
use App\Enums\ScamStatusType;
use App\Models\Scam;
use App\Models\ScamStatus;
use App\Notifications\ScamHoldReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnassignScamsWithStatus
{
    protected Carbon $scamsFrom;

    public function __construct()
    {
        $this->scamsFrom = Carbon::parse('2025-05-28');
    }

    public function handle(): void
    {
        DB::transaction(function () {
            foreach ([ScamStatusType::SALES, ScamStatusType::DRAFTING] as $statusType) {
                $this->unassignScams($statusType);           
                $this->unassignIfNoStatusChange($statusType);
            }
            $this->holdStatus();
        });
    }

    /* ==============================
     | STATUS-BASED UNASSIGN
     ============================== */

    private function unassignScams(ScamStatusType $statusType): void
    {
        $unassignableStatuses = $this->getUnassignableStatuses($statusType);

        if ($unassignableStatuses->isEmpty()) {
            return;
        }

        $scams = $this->getScamsWithStatuses(
            $statusType,
            $unassignableStatuses->keys()
        );

        foreach ($scams as $scam) {
            $this->tryUnassign($scam, $statusType, $unassignableStatuses);
        }
    }

    private function getUnassignableStatuses(ScamStatusType $statusType)
    {
        return ScamStatus::where('type', $statusType)
            ->where('unassign_scam', true)
            ->where('unassign_scam_in_days', '>', 0)
            ->get(['id', 'unassign_scam_in_days'])
            ->keyBy('id');
    }

    private function getScamsWithStatuses(ScamStatusType $statusType, $statusIds)
    {
        $prefix = $statusType->value;

        return Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->whereIn("{$prefix}_status_id", $statusIds)
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();
    }

    private function tryUnassign(
        Scam $scam,
        ScamStatusType $statusType,
        $unassignableStatuses
    ): void {
        $prefix   = $statusType->value;
        $statusId = $scam->{"{$prefix}_status_id"};
        $status   = $unassignableStatuses->get($statusId);
        $lastUpdated = $scam->{"{$prefix}_status_updated_at"};

        if (
            ! $status ||
            ! $lastUpdated ||
            $lastUpdated > now()->subDays($status->unassign_scam_in_days)
        ) {
            return;
        }

        $this->forceUnassign(
            $scam,
            $statusType,
            "Removed {$prefix} assignee (Due to status update days limit)",
            $statusId
        );
    }

    /* ==============================
     | NO STATUS SELECTED (1 DAY RULE)
     ============================== */

    private function unassignIfNoStatusChange(ScamStatusType $statusType): void
    {
        $prefix = $statusType->value;

        $scams = Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->whereNull("{$prefix}_status_id")
            ->whereNotNull("{$prefix}_assigned_at")
            ->where("{$prefix}_assigned_at", '<=', now()->subDay())
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();

        foreach ($scams as $scam) {
            $this->forceUnassign(
                $scam,
                $statusType,
                "Removed {$prefix} assignee (No status selected within 1 day)"
            );
        }
    }

    /* ==============================
     | SHARED FORCE UNASSIGN
     ============================== */

    private function forceUnassign(
        Scam $scam,
        ScamStatusType $statusType,
        string $description,
        ?int $statusId = null
    ): void {
        $prefix = $statusType->value;
        $originalAssigneeId = $scam->{"{$prefix}_assignee_id"};

        $scam->update([
            "{$prefix}_assignee_id" => null,
        ]);

        $scam->statusUnassignRecords()->create([
            'assignee_id' => $originalAssigneeId,
            'status_id'   => $statusId,
            'status_type' => $statusType,
        ]);

        $eventName = strtoupper("{$prefix}_assign");
        $event = constant("App\\Enums\\ScamActivityEvent::{$eventName}");

        $scam->logActivity($description, $event);
    }


    private function holdStatus(): void
    {
        $statusType = ScamStatusType::SALES;
        $prefix = $statusType->value;

        $holdStatus = ScamStatus::where('type', $statusType)
            ->where('slug', 'hold')
            ->first();

        if (!$holdStatus) {
            return;
        }

        // Send 2-day reminder before unassigning
        $this->notifyHoldReminder($holdStatus, $prefix);

        // Unassign scams held for >= 1 month
        $scams = Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->where("{$prefix}_status_id", $holdStatus->id)
            ->where("{$prefix}_status_updated_at", '<=', now()->subMonth())
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();

        foreach ($scams as $scam) {
            $this->forceUnassign(
                $scam,
                $statusType,
                "Removed {$prefix} assignee (Hold for more than 1 month)",
                $holdStatus->id
            );
        }
    }

    private function notifyHoldReminder(ScamStatus $holdStatus, string $prefix): void
    {
        // Find scams that will hit 1 month within the next 2 days (but not yet past 1 month)
        $lowerBound = now()->subMonth();
        $upperBound = now()->subMonth()->addDays(2);

        Log::info("[HoldReminder] Window: {$lowerBound} → {$upperBound}");

        $scams = Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->where("{$prefix}_status_id", $holdStatus->id)
            ->where("{$prefix}_status_updated_at", '<=', $upperBound)
            ->where("{$prefix}_status_updated_at", '>', $lowerBound)
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();

        Log::info("[HoldReminder] Matched scams: " . $scams->pluck('id')->join(', '));

        foreach ($scams as $scam) {
            $assignee = $scam->salesAssignee;
            if ($assignee) {
                $assignee->notify(new ScamHoldReminderNotification($scam));
                Log::info("[HoldReminder] Notification sent for scam #{$scam->id} to user #{$assignee->id}");
            }
        }
    }
}
