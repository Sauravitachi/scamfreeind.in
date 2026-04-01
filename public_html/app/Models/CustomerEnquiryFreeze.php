<?php

namespace App\Models;

use App\Enums\CustomerEnquiryStatusType;
use App\Enums\FreezeType;
use App\Enums\ScamStatusType;
use App\Foundation\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEnquiryFreeze extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'status_type' => ScamStatusType::class,
    ];

    public static function scopeWhereUser(Builder $query, User $user, string $statusType): void
    {
        $query->where('user_id', $user->id)->where('status_type', $statusType);
    }

    public static function checkAndTryFreezeRelease(CustomerEnquiry $enquiry, CustomerEnquiryStatusType $type): void
    {
        $assigneeColumn = "{$type->value}_assignee_id";
        $statusColumn = "{$type->value}_status_id";

        $userId = $enquiry->customer->scams()
            ->where('is_duplicate', false)
            ->whereNotNull('drafting_assignee_id')
            ->first()?->$assigneeColumn;

        $freeze = CustomerEnquiryFreeze::where('user_id', $userId)
            ->where('status_type', $type)->first();

        if ($freeze) {

            $freezeReleaseThreshold = setting('freeze_enquiry_release_threshold', null);

            if ($freezeReleaseThreshold !== null) {

                $enquiriesCount = CustomerEnquiry::where('occurrence', '>', 0)
                    ->where(function (Builder $q) use ($statusColumn, $type) {
                        $q->whereNull($statusColumn)
                            ->orWhereHas("{$type->value}Status", function (Builder $q2) {
                                $q2->where('consider_resolved', false);
                            });
                    })
                    ->{'where'.ucfirst($type->value).'Assignee'}($userId)->count();

                if ($enquiriesCount <= $freezeReleaseThreshold) {
                    // Release
                    $freeze->delete();
                    FreezeLog::create(['type' => FreezeType::ENQUIRY, 'freeze' => false, 'user_id' => $freeze->user_id]);
                }

            }

        }
    }

    /**
     * Get the user of the freeze
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
