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

    public function handle(): int
    {
        return DB::transaction(function () {
            $count = 0;
            foreach ([ScamStatusType::SALES, ScamStatusType::DRAFTING] as $statusType) {
                $count += $this->unassignScams($statusType);           
                $count += $this->unassignIfNoStatusChange($statusType);
            }
            $count += $this->unassignSalesIfNoUpdateFor18Hours();
            $count += $this->holdStatus();

            if ($count > 0) {
                Log::info("[ScamUnassign] Handled $count scams in total during this run.");
            }

            return $count;
        });
    }

    /* ==============================
     | STATUS-BASED UNASSIGN
     ============================== */

    private function unassignScams(ScamStatusType $statusType): int
    {
        $unassignableStatuses = $this->getUnassignableStatuses($statusType);

        if ($unassignableStatuses->isEmpty()) {
            return 0;
        }

        $scams = $this->getScamsWithStatuses(
            $statusType,
            $unassignableStatuses->keys()
        );

        $count = 0;
        foreach ($scams as $scam) {
            if ($this->tryUnassign($scam, $statusType, $unassignableStatuses)) {
                $count++;
            }
        }
        return $count;
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
    ): bool {
        $prefix   = $statusType->value;
        $statusId = $scam->{"{$prefix}_status_id"};
        $status   = $unassignableStatuses->get($statusId);
        $lastUpdated = $scam->{"{$prefix}_status_updated_at"};

        if (
            ! $status ||
            ! $lastUpdated ||
            $lastUpdated > now()->subDays($status->unassign_scam_in_days)
        ) {
            return false;
        }

        $this->forceUnassign(
            $scam,
            $statusType,
            "Removed {$prefix} assignee (Due to status update days limit)",
            $statusId
        );

        return true;
    }

    /* ==============================
     | NO STATUS SELECTED (1 DAY RULE)
     ============================== */

    private function unassignIfNoStatusChange(ScamStatusType $statusType): int
    {
        $prefix = $statusType->value;
        $threshold = ($statusType === ScamStatusType::SALES) ? now()->subHours(18) : now()->subDay();

        $scams = Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->whereNull("{$prefix}_status_id")
            ->whereNotNull("{$prefix}_assigned_at")
            ->where("{$prefix}_assigned_at", '<=', $threshold)
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();

        $count = 0;
        foreach ($scams as $scam) {
            $timeLabel = ($statusType === ScamStatusType::SALES) ? '18 hours' : '1 day';
            $this->forceUnassign(
                $scam,
                $statusType,
                "Removed {$prefix} assignee (No status selected within {$timeLabel})"
            );
            $count++;
        }
        return $count;
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

        // Clear both assignee and status to return to pool
        $scam->update([
            "{$prefix}_assignee_id" => null,
            "{$prefix}_status_id"   => null,
        ]);

        $eventName = strtoupper("{$prefix}_assign");
        $event = constant("App\\Enums\\ScamActivityEvent::{$eventName}");

        $scam->logActivity($description, $event);
        Log::info("[ScamUnassign] Scam #{$scam->id}: $description");
    }

    private function unassignSalesIfNoUpdateFor18Hours(): int
    {
        $prefix = ScamStatusType::SALES->value;
        $threshold = now()->subHours(18);

        // Find scams with a status that haven't been updated for 18 hours
        $scams = Scam::where('is_duplicate', false)
            ->whereNotNull("{$prefix}_assignee_id")
            ->whereNotNull("{$prefix}_status_id")
            ->where("{$prefix}_status_updated_at", '<=', $threshold)
            ->where('created_at', '>=', $this->scamsFrom)
            ->get();

        $count = 0;
        foreach ($scams as $scam) {
            $this->forceUnassign(
                $scam,
                ScamStatusType::SALES,
                "Removed sales assignee (No status update for 18 hours)",
                $scam->sales_status_id
            );
            $count++;
        }
        return $count;
    }


    private function holdStatus(): int
    {
        $statusType = ScamStatusType::SALES;
        $prefix = $statusType->value;

        $holdStatus = ScamStatus::where('type', $statusType)
            ->where('slug', 'hold')
            ->first();

        if (!$holdStatus) {
            return 0;
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

        $count = 0;
        foreach ($scams as $scam) {
            $this->forceUnassign(
                $scam,
                $statusType,
                "Removed {$prefix} assignee (Hold for more than 1 month)",
                $holdStatus->id
            );
            $count++;
        }
        return $count;
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
