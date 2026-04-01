<?php

namespace App\Actions\Scams;

use App\Enums\ScamActivityEvent;
use App\Enums\ScamStatusReview;
use App\Enums\ScamStatusType;
use App\Http\Requests\Admin\ChangeScamStatusReviewRequest;
use App\Models\Scam;
use Illuminate\Support\Facades\DB;

class ChangeScamStatusReview
{
    public function handle(Scam $scam, ChangeScamStatusReviewRequest $request): bool
    {
        return DB::transaction(function () use ($scam, $request) {

            $statusRecord = $scam->{$request->type.'StatusRecord'};

            abort_if($statusRecord === null, '400');

            $review = $request->validated('review');

            $scam->logActivity('Changed Scam Status Review : '.ucfirst($review), ScamActivityEvent::DRAFTING_STATUS);

            if ($review === ScamStatusReview::REJECTED->value) {

                $prevStatusRecord = $scam->previousStatusRecord(ScamStatusType::from($request->type), ['id', 'status_id']);

                request()->merge(['request:scam_status_rejected' => true]);

                $scam->update([
                    "{$request->type}_status_id" => $prevStatusRecord?->status?->id,
                ]);

                $scam->logActivity(
                    $prevStatusRecord?->status ? ucfirst($request->type)." status updated : {$prevStatusRecord->status->title}" : "Removed {$request->type} status",
                    ScamActivityEvent::tryFrom(strtoupper("{$request->type}_status}")) ?? ScamActivityEvent::DRAFTING_STATUS
                );

            }

            return $statusRecord->update([
                'review' => $review,
                'review_resolve_remark' => $request->validated('review_remark'),
                'review_resolved_at' => now(),
                'review_resolver_id' => $request->user()?->id,
            ]);

        });
    }
}
