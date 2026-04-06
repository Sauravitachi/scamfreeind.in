<?php

namespace App\Actions\CustomerEnquiry;

use App\Enums\CustomerEnquiryStatusType;
use App\Enums\ScamActivityEvent;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnassignEnquiriesWithStatus
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
            foreach ([CustomerEnquiryStatusType::SALES, CustomerEnquiryStatusType::DRAFTING] as $statusType) {
                $count += $this->unassignScams($statusType);
                $count += $this->unassignIfNoStatusChange($statusType);
            }

            $count += $this->unassignSalesIfNoUpdateFor18Hours();

            if ($count > 0) {
                Log::info("[EnquiryUnassign] Handled $count enquiries in total during this run.");
            }

            return $count;
        });
    }

    private function unassignScams(CustomerEnquiryStatusType $statusType): int
    {
        $unassignableStatuses = $this->getUnassignableStatuses($statusType);

        if (empty($unassignableStatuses)) {
            return 0;
        }

        $customerEnquiries = $this->getEnquiriesWithStatuses($statusType, $unassignableStatuses->keys());

        $count = 0;
        foreach ($customerEnquiries as $customerEnquiry) {
            if ($this->tryUnassign($customerEnquiry, $statusType, $unassignableStatuses)) {
                $count++;
            }
        }
        return $count;
    }

    private function getUnassignableStatuses(CustomerEnquiryStatusType $statusType)
    {
        return CustomerEnquiryStatus::where('type', $statusType)
            ->where('unassign_scam', true)
            ->where('unassign_scam_in_days', '>', 0)
            ->get(['id', 'unassign_scam_in_days'])
            ->keyBy('id');
    }

    private function getEnquiriesWithStatuses(CustomerEnquiryStatusType $statusType, $statusIds)
    {
        $prefix = $statusType->value;

        return CustomerEnquiry::whereIn("{$prefix}_status_id", $statusIds)
            ->where('occurrence', '>', 0)
            ->whereHas('customer.scams', function (Builder $q) use ($prefix) {
                $q->where('is_duplicate', false)
                    ->whereNotNull("{$prefix}_assignee_id");
            })
            ->get();
    }

    private function unassignIfNoStatusChange(CustomerEnquiryStatusType $statusType): int
    {
        $prefix = $statusType->value;
        $threshold = ($statusType === CustomerEnquiryStatusType::SALES) ? now()->subHours(18) : now()->subDay();

        $enquiries = CustomerEnquiry::whereNull("{$prefix}_status_id")
            ->where('occurrence', '>', 0)
            ->whereNotNull('manually_assigned_at')
            ->where('manually_assigned_at', '<=', $threshold)
            ->whereHas('customer.scams', function (Builder $q) use ($prefix) {
                $q->where('is_duplicate', false)
                    ->whereNotNull("{$prefix}_assignee_id");
            })
            ->get();

        $count = 0;
        foreach ($enquiries as $enquiry) {
            $scam = $enquiry->customer->scams->first();
            if ($scam) {
                $timeLabel = ($statusType === CustomerEnquiryStatusType::SALES) ? '18 hours' : '1 day';
                $this->forceUnassign(
                    $enquiry,
                    $scam,
                    $statusType,
                    "Removed {$prefix} assignee (No enquiry status selected within {$timeLabel})"
                );
                $count++;
            }
        }
        return $count;
    }

    private function unassignSalesIfNoUpdateFor18Hours(): int
    {
        $prefix = CustomerEnquiryStatusType::SALES->value;
        $threshold = now()->subHours(18);

        $enquiries = CustomerEnquiry::whereNotNull("{$prefix}_status_id")
            ->where('occurrence', '>', 0)
            ->where("{$prefix}_status_updated_at", '<=', $threshold)
            ->whereHas('customer.scams', function (Builder $q) use ($prefix) {
                $q->where('is_duplicate', false)
                    ->whereNotNull("{$prefix}_assignee_id");
            })
            ->get();

        $count = 0;
        foreach ($enquiries as $enquiry) {
            $scam = $enquiry->customer->scams->first();
            if ($scam) {
                $this->forceUnassign(
                    $enquiry,
                    $scam,
                    CustomerEnquiryStatusType::SALES,
                    "Removed sales assignee (No enquiry status update for 18 hours)",
                    $enquiry->{"{$prefix}_status_id"}
                );
                $count++;
            }
        }
        return $count;
    }

    private function tryUnassign(CustomerEnquiry $customerEnquiry, CustomerEnquiryStatusType $statusType, $unassignableStatuses): bool
    {
        $scam = $customerEnquiry->customer->scams->first();

        if (! $scam) {
            return false;
        }

        $prefix = $statusType->value;
        $statusId = $customerEnquiry->{"{$prefix}_status_id"};
        $status = $unassignableStatuses->get($statusId);

        $lastUpdated = $customerEnquiry->{"{$prefix}_status_updated_at"};

        if (! $status || $lastUpdated > now()->subDays($status->unassign_scam_in_days)) {
            return false;
        }

        $this->forceUnassign(
            $customerEnquiry,
            $scam,
            $statusType,
            "Removed {$prefix} assignee (Due to status update days limit on the enquiry)",
            $statusId
        );

        return true;
    }

    private function forceUnassign(
        CustomerEnquiry $customerEnquiry,
        \App\Models\Scam $scam,
        CustomerEnquiryStatusType $statusType,
        string $description,
        ?int $statusId = null
    ): void {
        $prefix = $statusType->value;
        $originalAssigneeId = $scam->{"{$prefix}_assignee_id"};

        // Clear both assignee and status on the scam
        $scam->update([
            "{$prefix}_assignee_id" => null,
            "{$prefix}_status_id" => null,
        ]);

        // Clear status on the enquiry as well
        $customerEnquiry->update([
            "{$prefix}_status_id" => null,
        ]);

        $scam->statusUnassignRecords()->create([
            'assignee_id' => $originalAssigneeId,
            'enquiry_status_id' => $statusId,
            'status_type' => $statusType->value,
        ]);

        $eventName = strtoupper("{$prefix}_assign");
        $event = constant(\App\Enums\ScamActivityEvent::class . "::{$eventName}");
        $scam->logActivity($description, $event);
        Log::info("[EnquiryUnassign] Enquiry #{$customerEnquiry->id} (Scam #{$scam->id}): $description");
    }
}
