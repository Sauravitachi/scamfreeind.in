<?php

namespace App\Actions\CustomerEnquiry;

use App\Enums\CustomerEnquiryStatusType;
use App\Enums\ScamActivityEvent;
use App\Models\CustomerEnquiry;
use App\Models\CustomerEnquiryStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UnassignEnquiriesWithStatus
{
    protected Carbon $scamsFrom;

    public function __construct()
    {
        $this->scamsFrom = Carbon::parse('2025-05-28');
    }

    public function handle(): void
    {
        DB::transaction(function () {
            foreach ([CustomerEnquiryStatusType::SALES, CustomerEnquiryStatusType::DRAFTING] as $statusType) {
                $this->unassignScams($statusType);
            }
        });
    }

    private function unassignScams(CustomerEnquiryStatusType $statusType): void
    {
        $unassignableStatuses = $this->getUnassignableStatuses($statusType);

        if (empty($unassignableStatuses)) {
            return;
        }

        $customerEnquiries = $this->getEnquiriesWithStatuses($statusType, $unassignableStatuses->keys());

        foreach ($customerEnquiries as $customerEnquiry) {
            $this->tryUnassign($customerEnquiry, $statusType, $unassignableStatuses);
        }
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
                    ->whereNotNull("{$prefix}_assignee_id")
                    // ->where('created_at', '>=', $this->scamsFrom)
                    ->limit(1);
            })
            ->get();
    }

    private function tryUnassign(CustomerEnquiry $customerEnquiry, CustomerEnquiryStatusType $statusType, $unassignableStatuses): void
    {
        $scam = $customerEnquiry->customer->scams->first();

        if (! $scam) {
            return;
        }

        $prefix = $statusType->value;
        $statusId = $customerEnquiry->{"{$prefix}_status_id"};
        $status = $unassignableStatuses->get($statusId);

        $lastUpdated = $customerEnquiry->{"{$prefix}_status_updated_at"};

        if (! $status || $lastUpdated > now()->subDays($status->unassign_scam_in_days)) {
            return;
        }

        $originalAssigneeId = $scam->{"{$prefix}_assignee_id"};
        $scam->update(["{$prefix}_assignee_id" => null]);

        $scam->statusUnassignRecords()->create([
            'assignee_id' => $originalAssigneeId,
            'enquiry_status_id' => $statusId,
            'status_type' => $statusType->value,
        ]);

        $eventName = strtoupper("{$prefix}_assign");
        $event = constant(\App\Enums\ScamActivityEvent::class . "::{$eventName}");
        $description = "Removed {$prefix} assignee (Due to status update days limit on the enquiry)";
        $scam->logActivity($description, $event);
    }
}
